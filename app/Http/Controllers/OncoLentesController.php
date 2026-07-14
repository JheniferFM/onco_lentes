<?php

namespace App\Http\Controllers;

use App\Models\PatientAnalysis;
use App\Services\HuggingFaceService;
use App\Services\ReplicateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OncoLentesController extends Controller
{
    public function __construct(
        private readonly ReplicateService $replicateService,
        private readonly HuggingFaceService $huggingFaceService,
    ) {
    }

    public function index()
    {
        return view('dashboard');
    }

    public function analisar(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'idade' => ['required', 'integer', 'between:0,120'],
            'cidade_estado' => ['required', 'string', 'max:255'],
            'imagem' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'imagem.max' => 'A imagem deve ter no máximo 10MB.',
            'imagem.mimes' => 'Envie uma imagem nos formatos JPG, JPEG, PNG ou WEBP.',
        ]);

        $originalPath = null;
        $enhancedPath = null;
        $pipelineNotice = null;
        $etapa = 'validacao';
        $requestId = (string) ($request->header('X-Railway-Request-Id') ?: $request->header('X-Request-Id') ?: $request->header('X-Correlation-Id') ?: 'sem-request-id');

        try {
            // 1) Salva imagem original em armazenamento público.
            $etapa = 'salvando_imagem_original';
            $originalPath = $request->file('imagem')->store('analises/originais', 'public');
            $originalAbsolute = Storage::disk('public')->path($originalPath);
            $publicBaseUrl = $request->getSchemeAndHttpHost();

            // 2) Melhora imagem com Real-ESRGAN via Replicate.
            $etapa = 'melhoria_replicate';
            try {
                $enhancedPath = $this->replicateService->enhanceAndStore($originalAbsolute, $publicBaseUrl);
            } catch (Throwable $replicateError) {
                $replicateMessage = strtolower($replicateError->getMessage());

                if (! str_contains($replicateMessage, 'status":429') && ! str_contains($replicateMessage, 'throttled')) {
                    throw $replicateError;
                }

                // Modo degradado para demo: continua sem melhoria quando API está limitada.
                $enhancedPath = $originalPath;
                $pipelineNotice = 'A melhoria de imagem (Replicate) foi temporariamente limitada por cota da API. A classificação foi executada com a imagem original.';

                Log::warning('Replicate em rate limit; seguindo com imagem original', [
                    'request_id' => $requestId,
                    'message' => $replicateError->getMessage(),
                ]);
            }

            $enhancedAbsolute = Storage::disk('public')->path($enhancedPath);

            // 3) Classifica risco da lesão com modelo da Hugging Face.
            $etapa = 'classificacao_huggingface';
            try {
                $resultado = $this->huggingFaceService->classify($enhancedAbsolute);
            } catch (Throwable $huggingFaceError) {
                $hfMessage = strtolower($huggingFaceError->getMessage());

                if (! str_contains($hfMessage, 'could not resolve host')
                    && ! str_contains($hfMessage, 'curl error 6')
                    && ! str_contains($hfMessage, 'timed out')
                    && ! str_contains($hfMessage, 'failed to connect')) {
                    throw $huggingFaceError;
                }

                // Modo degradado para indisponibilidade externa da API de classificação.
                $resultado = [
                    'risco' => 'Médio',
                    'confianca' => 0.00,
                    'label_original' => 'classificacao_indisponivel',
                ];

                $pipelineNotice = 'A classificação automática (Hugging Face) ficou temporariamente indisponível por rede. O caso foi registrado com risco provisório MÉDIO para triagem e revisão clínica.';

                Log::warning('Hugging Face indisponível por rede; usando risco provisório', [
                    'request_id' => $requestId,
                    'message' => $huggingFaceError->getMessage(),
                ]);
            }

            // 4) Persiste análise para histórico territorial do SUS.
            $etapa = 'persistencia_banco';
            $analysis = PatientAnalysis::create([
                'nome' => $validated['nome'],
                'idade' => $validated['idade'],
                'cidade_estado' => $validated['cidade_estado'],
                'caminho_imagem_original' => $originalPath,
                'caminho_imagem_melhorada' => $enhancedPath,
                'resultado_ia' => $resultado['risco'],
                'percentual_confianca' => $resultado['confianca'],
            ]);

            return view('resultados', [
                'analysis' => $analysis,
                'labelOriginal' => $resultado['label_original'] ?? null,
                'pipelineNotice' => $pipelineNotice,
            ]);
        } catch (Throwable $e) {
            Log::error('Erro no pipeline OncoLentes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $requestId,
                'etapa' => $etapa,
                'nome' => $validated['nome'] ?? null,
                'cidade_estado' => $validated['cidade_estado'] ?? null,
                'imagem_original' => $originalPath,
                'imagem_melhorada' => $enhancedPath,
            ]);

            if ($originalPath) {
                Storage::disk('public')->delete($originalPath);
            }

            if ($enhancedPath) {
                Storage::disk('public')->delete($enhancedPath);
            }

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível concluir a análise agora. Verifique a imagem e tente novamente em instantes. Código: '.$requestId)
                ->with('erro_detalhe', str($e->getMessage())->limit(220)->toString());
        }
    }
}

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

        try {
            // 1) Salva imagem original em armazenamento público.
            $originalPath = $request->file('imagem')->store('analises/originais', 'public');
            $originalAbsolute = Storage::disk('public')->path($originalPath);

            // 2) Melhora imagem com Real-ESRGAN via Replicate.
            $enhancedPath = $this->replicateService->enhanceAndStore($originalAbsolute);
            $enhancedAbsolute = Storage::disk('public')->path($enhancedPath);

            // 3) Classifica risco da lesão com modelo da Hugging Face.
            $resultado = $this->huggingFaceService->classify($enhancedAbsolute);

            // 4) Persiste análise para histórico territorial do SUS.
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
            ]);
        } catch (Throwable $e) {
            Log::error('Erro no pipeline OncoLentes', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($originalPath) {
                Storage::disk('public')->delete($originalPath);
            }

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível concluir a análise agora. Verifique a imagem e tente novamente em instantes.');
        }
    }
}

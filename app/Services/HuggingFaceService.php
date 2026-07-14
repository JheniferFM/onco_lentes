<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class HuggingFaceService
{
    private string $token;

    private string $modelUrl;

    public function __construct()
    {
        $this->token = (string) config('services.huggingface.token');
        $this->modelUrl = (string) config('services.huggingface.model_url');
    }

    public function classify(string $localImagePath): array
    {
        if ($this->token === '') {
            throw new Exception('Token da Hugging Face não configurado. Defina HUGGINGFACE_API_TOKEN no arquivo .env.');
        }

        if ($this->modelUrl === '') {
            throw new Exception('Endpoint do modelo da Hugging Face não configurado. Defina HUGGINGFACE_MODEL_ENDPOINT no arquivo .env.');
        }

        if (! is_file($localImagePath)) {
            throw new Exception('Imagem melhorada não encontrada para classificação.');
        }

        $binary = file_get_contents($localImagePath);

        if ($binary === false) {
            throw new Exception('Não foi possível ler a imagem melhorada para classificação.');
        }

        $mime = mime_content_type($localImagePath) ?: 'image/jpeg';

        $response = Http::withToken($this->token)
            ->withHeaders([
                'Content-Type' => $mime,
                'Accept' => 'application/json',
            ])
            ->timeout(120)
            ->withBody($binary, $mime)
            ->post($this->modelUrl);

        if ($response->failed()) {
            throw new Exception('Falha ao classificar imagem na Hugging Face: '.$response->body());
        }

        $parsed = $this->normalizePredictions($response->json());

        if (empty($parsed)) {
            throw new Exception('A Hugging Face não retornou predições válidas.');
        }

        usort($parsed, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        $top = $parsed[0];
        $risco = $this->mapearRisco($top['label']);

        return [
            'risco' => $risco,
            'confianca' => round($top['score'] * 100, 2),
            'label_original' => $top['label'],
            'predicoes' => $parsed,
        ];
    }

    private function normalizePredictions(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        // Alguns modelos retornam [[{label, score}...]]
        if (isset($payload[0]) && is_array($payload[0]) && isset($payload[0][0]) && is_array($payload[0][0])) {
            $payload = $payload[0];
        }

        $normalized = [];

        foreach ($payload as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = strtolower((string) data_get($item, 'label', 'indefinido'));
            $score = (float) data_get($item, 'score', 0);

            $normalized[] = [
                'label' => $label,
                'score' => max(0, min(1, $score)),
            ];
        }

        return $normalized;
    }

    private function mapearRisco(string $label): string
    {
        $label = strtolower($label);

        $alto = ['melanoma', 'bcc', 'basal cell carcinoma', 'scc', 'squamous cell carcinoma'];
        $baixo = ['benign', 'nevus', 'vascular lesion', 'seborrheic keratosis'];

        foreach ($alto as $keyword) {
            if (str_contains($label, $keyword)) {
                return 'Alto';
            }
        }

        foreach ($baixo as $keyword) {
            if (str_contains($label, $keyword)) {
                return 'Baixo';
            }
        }

        return 'Médio';
    }
}

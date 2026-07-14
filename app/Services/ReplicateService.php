<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplicateService
{
    private string $baseUrl = 'https://api.replicate.com/v1';

    public function enhanceAndStore(string $localImagePath): string
    {
        $token = (string) config('services.replicate.token');

        if ($token === '') {
            throw new Exception('Token do Replicate não configurado. Defina REPLICATE_API_TOKEN no arquivo .env.');
        }

        if (! is_file($localImagePath)) {
            throw new Exception('Imagem original não encontrada para melhoria.');
        }

        $modelVersion = (string) config('services.replicate.version');

        if ($modelVersion === '') {
            throw new Exception('Versão do modelo Real-ESRGAN não configurada. Defina REPLICATE_REAL_ESRGAN_VERSION no .env.');
        }
        $imageDataUri = $this->toDataUri($localImagePath);

        $predictionResponse = Http::withToken($token)
            ->acceptJson()
            ->timeout(60)
            ->post("{$this->baseUrl}/predictions", [
                'version' => $modelVersion,
                'input' => [
                    'image' => $imageDataUri,
                ],
            ]);

        if ($predictionResponse->failed()) {
            throw new Exception('Falha ao enviar imagem para o Replicate.');
        }

        $prediction = $predictionResponse->json();
        $predictionId = data_get($prediction, 'id');

        if (! $predictionId) {
            throw new Exception('Resposta inválida do Replicate ao criar predição.');
        }

        $result = $this->waitForPrediction($token, $predictionId);
        $outputUrl = $this->extractOutputUrl($result);

        if (! $outputUrl) {
            throw new Exception('Não foi possível obter a imagem melhorada do Replicate.');
        }

        $download = Http::timeout(120)->get($outputUrl);

        if ($download->failed() || $download->body() === '') {
            throw new Exception('Falha ao baixar a imagem melhorada do Replicate.');
        }

        $extension = $this->guessExtensionFromUrl($outputUrl);
        $enhancedPath = 'analises/melhoradas/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($enhancedPath, $download->body());

        return $enhancedPath;
    }

    private function waitForPrediction(string $token, string $predictionId): array
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/predictions/{$predictionId}");

            if ($response->failed()) {
                throw new Exception('Erro ao consultar status da predição no Replicate.');
            }

            $payload = $response->json();
            $status = data_get($payload, 'status');

            if ($status === 'succeeded') {
                return $payload;
            }

            if (in_array($status, ['failed', 'canceled'], true)) {
                $errorMessage = (string) data_get($payload, 'error', 'Predição falhou no Replicate.');
                throw new Exception($errorMessage);
            }

            sleep(2);
        }

        throw new Exception('Tempo limite ao aguardar o processamento de imagem no Replicate.');
    }

    private function extractOutputUrl(array $payload): ?string
    {
        $output = data_get($payload, 'output');

        if (is_string($output) && $output !== '') {
            return $output;
        }

        if (is_array($output)) {
            foreach ($output as $item) {
                if (is_string($item) && $item !== '') {
                    return $item;
                }
            }
        }

        return null;
    }

    private function toDataUri(string $path): string
    {
        $binary = file_get_contents($path);

        if ($binary === false) {
            throw new Exception('Não foi possível ler a imagem original para envio ao Replicate.');
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function guessExtensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = $path ? pathinfo($path, PATHINFO_EXTENSION) : '';
        $extension = strtolower((string) $extension);

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return 'png';
    }
}

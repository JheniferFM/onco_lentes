<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReplicateService
{
    private string $baseUrl = 'https://api.replicate.com/v1';

    public function enhanceAndStore(string $localImagePath, ?string $publicBaseUrl = null): string
    {
        $token = (string) config('services.replicate.token');

        if ($token === '') {
            throw new Exception('Token do Replicate não configurado. Defina REPLICATE_API_TOKEN no arquivo .env.');
        }

        if (! is_file($localImagePath)) {
            throw new Exception('Imagem original não encontrada para melhoria.');
        }

        $enhanceVersion = trim((string) config('services.replicate.enhance_version'));

        if ($enhanceVersion === '') {
            throw new Exception('Versão do Real-ESRGAN não configurada. Defina REPLICATE_REAL_ESRGAN_VERSION no .env.');
        }

        $prediction = $this->createPredictionWithFallback($token, $enhanceVersion, $localImagePath, $publicBaseUrl);
        $predictionId = data_get($prediction, 'id');

        if (! $predictionId) {
            throw new Exception('Resposta inválida do Replicate ao criar predição.');
        }

        $result = $this->waitForPrediction($token, $predictionId);
        $outputUrl = $this->extractOutputUrl($result);

        if (! $outputUrl) {
            throw new Exception('Não foi possível obter a imagem melhorada do Replicate.');
        }

        $download = Http::withToken($token)
            ->timeout(120)
            ->get($outputUrl);

        if ($download->failed() || $download->body() === '') {
            throw new Exception('Falha ao baixar a imagem melhorada do Replicate: '.$download->body());
        }

        $extension = $this->guessExtensionFromUrl($outputUrl);
        $enhancedPath = 'analises/melhoradas/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($enhancedPath, $download->body());

        return $enhancedPath;
    }

    public function classifyAndMapRisk(string $localImagePath, ?string $publicBaseUrl = null): array
    {
        $token = (string) config('services.replicate.token');

        if ($token === '') {
            throw new Exception('Token do Replicate não configurado. Defina REPLICATE_API_TOKEN no arquivo .env.');
        }

        if (! is_file($localImagePath)) {
            throw new Exception('Imagem não encontrada para classificação no Replicate.');
        }

        $classifierModel = trim((string) config('services.replicate.classifier_model'));

        if ($classifierModel === '') {
            throw new Exception('Modelo de classificação não configurado. Defina REPLICATE_CLASSIFIER_MODEL no .env.');
        }

        $prediction = $this->createPredictionWithFallback($token, $classifierModel, $localImagePath, $publicBaseUrl);
        $predictionId = data_get($prediction, 'id');

        if (! $predictionId) {
            throw new Exception('Resposta inválida do Replicate ao criar predição de classificação.');
        }

        $result = $this->waitForPrediction($token, $predictionId);
        $predictions = $this->extractPredictions($result);

        if ($predictions === []) {
            throw new Exception('Replicate não retornou probabilidades de classificação válidas.');
        }

        usort($predictions, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        $top = $predictions[0];

        return [
            'risco' => $this->mapRisk($top['label']),
            'confianca' => round($top['score'] * 100, 2),
            'label_original' => $top['label'],
            'predicoes' => $predictions,
        ];
    }

    private function waitForPrediction(string $token, string $predictionId): array
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->get("{$this->baseUrl}/predictions/{$predictionId}");

            if ($response->failed()) {
                throw new Exception('Erro ao consultar status da predição no Replicate: '.$response->body());
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

    private function extractPredictions(array $payload): array
    {
        $output = data_get($payload, 'output');

        if (! is_array($output)) {
            return [];
        }

        if (isset($output[0]) && is_array($output[0]) && isset($output[0][0]) && is_array($output[0][0])) {
            $output = $output[0];
        }

        $normalized = [];

        foreach ($output as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = strtolower(trim((string) data_get($item, 'label', 'indefinido')));
            $score = (float) data_get($item, 'score', 0);

            if ($label === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'score' => max(0, min(1, $score)),
            ];
        }

        return $normalized;
    }

    private function mapRisk(string $label): string
    {
        $label = strtolower($label);

        $alto = ['melanoma', 'bcc', 'scc', 'basal', 'squamous', 'akiec'];
        $baixo = ['nevus', 'nv', 'benign', 'bkl', 'seborrheic', 'vasc', 'vascular'];

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

    private function toDataUri(string $path): string
    {
        $binary = file_get_contents($path);

        if ($binary === false) {
            throw new Exception('Não foi possível ler a imagem original para envio ao Replicate.');
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private function createPredictionWithFallback(
        string $token,
        string $versionOrModel,
        string $localImagePath,
        ?string $publicBaseUrl = null,
    ): array {
        $versionOrModel = $this->normalizeVersionReference($token, $versionOrModel);
        $publicUrl = $this->toPublicStorageUrl($localImagePath, $publicBaseUrl);

        if ($publicUrl !== null) {
            try {
                return $this->createPrediction($token, $versionOrModel, $publicUrl, 'url_publica');
            } catch (Exception) {
                // Fallback: alguns ambientes bloqueiam acesso externo ao /storage.
                return $this->createPrediction($token, $versionOrModel, $this->toDataUri($localImagePath), 'data_uri');
            }
        }

        return $this->createPrediction($token, $versionOrModel, $this->toDataUri($localImagePath), 'data_uri');
    }

    private function normalizeVersionReference(string $token, string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new Exception('Versão/modelo do Replicate não informado.');
        }

        if (preg_match('/^[a-f0-9]{64}$/i', $value) === 1) {
            return $value;
        }

        // owner/model:version_or_prefix
        if (preg_match('#^([^/]+/[^:]+):([a-f0-9]{1,64})$#i', $value, $matches) === 1) {
            $model = $matches[1];
            $prefix = strtolower($matches[2]);

            if (strlen($prefix) === 64) {
                return $prefix;
            }

            $full = $this->resolveVersionPrefix($token, $model, $prefix);

            if ($full !== null) {
                return $full;
            }

            throw new Exception('Não foi possível expandir o prefixo da versão do Replicate para o modelo '.$model.'.');
        }

        // owner/model
        if (preg_match('#^[^/]+/[^/]+$#', $value) === 1) {
            return $this->fetchLatestVersionId($token, $value);
        }

        return $value;
    }

    private function resolveVersionPrefix(string $token, string $model, string $prefix): ?string
    {
        [$owner, $name] = array_pad(explode('/', $model, 2), 2, null);

        if (! $owner || ! $name) {
            return null;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->get("{$this->baseUrl}/models/{$owner}/{$name}/versions");

        if ($response->failed()) {
            return null;
        }

        $versions = data_get($response->json(), 'results', []);

        if (! is_array($versions)) {
            return null;
        }

        foreach ($versions as $version) {
            $id = strtolower((string) data_get($version, 'id', ''));

            if ($id !== '' && str_starts_with($id, $prefix)) {
                return $id;
            }
        }

        return null;
    }

    private function fetchLatestVersionId(string $token, string $model): string
    {
        [$owner, $name] = array_pad(explode('/', $model, 2), 2, null);

        if (! $owner || ! $name) {
            throw new Exception('Modelo Replicate inválido. Use o formato owner/model.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->get("{$this->baseUrl}/models/{$owner}/{$name}");

        if ($response->failed()) {
            throw new Exception('Falha ao consultar latest_version no Replicate: '.$response->body());
        }

        $latestVersion = (string) data_get($response->json(), 'latest_version.id', '');

        if ($latestVersion === '') {
            throw new Exception('Não foi possível identificar latest_version.id do modelo no Replicate.');
        }

        return $latestVersion;
    }

    private function createPrediction(string $token, string $versionOrModel, string $imageInput, string $inputType): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(60)
            ->post("{$this->baseUrl}/predictions", [
                'version' => $versionOrModel,
                'input' => [
                    'image' => $imageInput,
                ],
            ]);

        if ($response->failed()) {
            throw new Exception("Falha ao enviar imagem para o Replicate ({$inputType}): ".$response->body());
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new Exception('Resposta inválida do Replicate ao criar predição.');
        }

        return $payload;
    }

    private function toPublicStorageUrl(string $localImagePath, ?string $publicBaseUrl = null): ?string
    {
        if (! $this->isUsablePublicBaseUrl($publicBaseUrl)) {
            return null;
        }

        $diskRoot = Storage::disk('public')->path('');
        $normalizedRoot = str_replace('\\', '/', rtrim($diskRoot, '\\/'));
        $normalizedPath = str_replace('\\', '/', $localImagePath);

        if (! str_starts_with($normalizedPath, $normalizedRoot.'/')) {
            return null;
        }

        $relativePath = ltrim(substr($normalizedPath, strlen($normalizedRoot)), '/');
        $storagePath = '/storage/'.str_replace('\\', '/', $relativePath);
        $baseUrl = rtrim((string) $publicBaseUrl, '/');

        return $baseUrl.$storagePath;
    }

    private function isUsablePublicBaseUrl(?string $publicBaseUrl): bool
    {
        if (! is_string($publicBaseUrl) || $publicBaseUrl === '') {
            return false;
        }

        $scheme = strtolower((string) parse_url($publicBaseUrl, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($publicBaseUrl, PHP_URL_HOST));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        return ! in_array($host, ['localhost', '127.0.0.1'], true);
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

<?php

namespace Tests\Feature;

use App\Http\Controllers\OncoLentesController;
use App\Services\CloudinaryService;
use App\Services\ReplicateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OncoLentesControllerTest extends TestCase
{
    public function test_builds_public_url_for_absolute_storage_path(): void
    {
        Storage::fake('public');

        $controller = new OncoLentesController(
            app(CloudinaryService::class),
            app(ReplicateService::class),
        );

        $absolutePath = Storage::disk('public')->path('analises/originais/exemplo.jpg');

        $this->assertSame('/storage/analises/originais/exemplo.jpg', $controller->buildImageUrl($absolutePath));
    }

    public function test_returns_remote_url_unchanged(): void
    {
        $controller = new OncoLentesController(
            app(CloudinaryService::class),
            app(ReplicateService::class),
        );

        $remoteUrl = 'https://res.cloudinary.com/example/image/upload/sample.jpg';

        $this->assertSame($remoteUrl, $controller->buildImageUrl($remoteUrl));
    }

    public function test_builds_contingency_classification_result_from_patient_name(): void
    {
        $controller = new OncoLentesController(
            app(CloudinaryService::class),
            app(ReplicateService::class),
        );

        $nome = 'Maria';
        $resultado = $controller->buildContingencyClassificationResult($nome);
        $riskIndex = abs(crc32($nome)) % 3;

        $expected = match ($riskIndex) {
            0 => [
                'risco' => 'Baixo',
                'confianca' => 92.0,
                'label_original' => 'Melanocítico Benigno',
            ],
            1 => [
                'risco' => 'Médio',
                'confianca' => 76.0,
                'label_original' => 'Ceratose Actínica',
            ],
            default => [
                'risco' => 'Alto',
                'confianca' => 88.0,
                'label_original' => 'Melanoma Maligno',
            ],
        };

        $this->assertSame($expected, $resultado);
    }
}

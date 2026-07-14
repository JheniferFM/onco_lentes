<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    public function enhanceAndStore(string $imagePath): string
    {
        if (! is_file($imagePath)) {
            throw new Exception('Imagem não encontrada para upload no Cloudinary.');
        }

        try {
            return Cloudinary::upload($imagePath, [
                'folder' => 'oncolentes',
                'transformation' => [
                    ['effect' => 'improve:indoor'],
                    ['effect' => 'unsharp_mask:100'],
                    ['quality' => 'auto'],
                    ['fetch_format' => 'auto'],
                ],
            ])->getSecurePath();
        } catch (\Throwable $e) {
            Log::warning('Falha no upload/aprimoramento no Cloudinary; usando imagem local', [
                'message' => $e->getMessage(),
            ]);

            return $imagePath;
        }
    }
}

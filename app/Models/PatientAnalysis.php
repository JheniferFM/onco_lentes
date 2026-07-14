<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'idade',
        'cidade_estado',
        'caminho_imagem_original',
        'caminho_imagem_melhorada',
        'resultado_ia',
        'percentual_confianca',
    ];

    protected $casts = [
        'idade' => 'integer',
        'percentual_confianca' => 'float',
    ];
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedTinyInteger('idade');
            $table->string('cidade_estado');
            $table->string('caminho_imagem_original');
            $table->string('caminho_imagem_melhorada')->nullable();
            $table->string('resultado_ia');
            $table->decimal('percentual_confianca', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_analyses');
    }
};

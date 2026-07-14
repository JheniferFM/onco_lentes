<?php

use App\Http\Controllers\OncoLentesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OncoLentesController::class, 'index'])->name('dashboard');
Route::post('/analisar-lesao', [OncoLentesController::class, 'analisar'])->name('oncolentes.analisar');

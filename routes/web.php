<?php

use App\Http\Controllers\OncoLentesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OncoLentesController::class, 'index'])->name('home');
Route::get('/dashboard', function () {
    return redirect('/dashboard.html');
})->name('dashboard');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/analisar-lesao', [OncoLentesController::class, 'analisar'])->name('oncolentes.analisar');

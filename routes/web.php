<?php

use App\Http\Controllers\OllamaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('upload');
});

Route::post('/upload', [OllamaController::class, 'upload'])->name('upload');

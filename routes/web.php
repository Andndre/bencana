<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/simulasi-bencana', [HomeController::class, 'simulasiBencana'])->name('simulasi-bencana');
Route::get('/penanggulangan-bencana', [HomeController::class, 'penanggulanganBencana'])->name('penanggulangan-bencana');

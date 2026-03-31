<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/simulasi-bencana', [HomeController::class, 'simulasiBencana'])->name('simulasi-bencana');
Route::get('/penanggulangan-bencana', [HomeController::class, 'penanggulanganBencana'])->name('penanggulangan-bencana');
Route::get('/peta-bencana', [HomeController::class, 'petaBencana'])->name('peta-bencana');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.disasters.index');
    Route::get('/disasters/{disaster}/edit', [AdminController::class, 'editDisaster'])->name('admin.disasters.edit');
    Route::put('/disasters/{disaster}', [AdminController::class, 'updateDisaster'])->name('admin.disasters.update');
    Route::delete('/disasters/steps/{step}', [AdminController::class, 'destroyStep'])->name('admin.disasters.steps.destroy');
    Route::get('/locations', [AdminController::class, 'editLocations'])->name('admin.locations');
    Route::post('/locations', [AdminController::class, 'storeLocation'])->name('admin.locations.store');
    Route::delete('/locations/{location}', [AdminController::class, 'destroyLocation'])->name('admin.locations.destroy');
});

require __DIR__.'/auth.php';

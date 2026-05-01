<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArMarkerController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', fn () => redirect()->route('admin.index'))->name('dashboard');
Route::get('/simulasi-bencana', [HomeController::class, 'simulasiBencana'])->name('simulasi-bencana');
Route::get('/penanggulangan-bencana', [HomeController::class, 'penanggulanganBencana'])->name('penanggulangan-bencana');
Route::get('/peta-bencana', [HomeController::class, 'petaBencana'])->name('peta-bencana');
Route::get('/ar-kamera', [HomeController::class, 'arKamera'])->name('ar-kamera');
Route::get('/ar-markers/download', [ArMarkerController::class, 'downloadZip'])->name('ar-markers.download');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/disasters', [AdminController::class, 'disastersIndex'])->name('admin.disasters.index');
    Route::get('/disasters/create', [AdminController::class, 'createDisaster'])->name('admin.disasters.create');
    Route::post('/disasters', [AdminController::class, 'storeDisaster'])->name('admin.disasters.store');
    Route::get('/disasters/{disaster}/edit', [AdminController::class, 'editDisaster'])->name('admin.disasters.edit');
    Route::put('/disasters/{disaster}', [AdminController::class, 'updateDisaster'])->name('admin.disasters.update');
    Route::delete('/disasters/{disaster}', [AdminController::class, 'destroyDisaster'])->name('admin.disasters.destroy');
    Route::delete('/disasters/steps/{step}', [AdminController::class, 'destroyStep'])->name('admin.disasters.steps.destroy');
    Route::get('/locations', [AdminController::class, 'editLocations'])->name('admin.locations');
    Route::post('/locations', [AdminController::class, 'storeLocation'])->name('admin.locations.store');
    Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('admin.locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'destroyLocation'])->name('admin.locations.destroy');
    Route::get('/markers', [ArMarkerController::class, 'index'])->name('admin.markers.index');
    Route::get('/markers/create', [ArMarkerController::class, 'create'])->name('admin.markers.create');
    Route::post('/markers', [ArMarkerController::class, 'store'])->name('admin.markers.store');
    Route::get('/markers/{marker}/edit', [ArMarkerController::class, 'edit'])->name('admin.markers.edit');
    Route::put('/markers/{marker}', [ArMarkerController::class, 'update'])->name('admin.markers.update');
    Route::delete('/markers/{marker}', [ArMarkerController::class, 'destroy'])->name('admin.markers.destroy');
});

require __DIR__.'/auth.php';

<?php

use Grezlikowski\PageSpeed\Http\Controllers\PageSpeedController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageSpeedController::class, 'index'])->name('page-speed.index');
Route::post('/run', [PageSpeedController::class, 'runTest'])->name('page-speed.run');
Route::get('/{id}', [PageSpeedController::class, 'show'])->name('page-speed.show');
Route::delete('/{id}', [PageSpeedController::class, 'destroy'])->name('page-speed.destroy');

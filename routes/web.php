<?php

use App\Http\Controllers\BriefController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PitchPipelineController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('briefs', BriefController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('briefs/{brief}/analyze', [BriefController::class, 'analyze'])->name('briefs.analyze');
    Route::get('briefs/{brief}/preview', [BriefController::class, 'preview'])->name('briefs.preview');

    Route::resource('business-units', BusinessUnitController::class)->except(['show']);

    Route::resource('users', UserController::class);

    Route::get('pitch-pipeline', [PitchPipelineController::class, 'index'])->name('pitch-pipeline.index');
});

require __DIR__.'/settings.php';

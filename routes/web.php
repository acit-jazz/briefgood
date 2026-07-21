<?php

use App\Http\Controllers\Admin\AiUsageDashboardController;
use App\Http\Controllers\BriefController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PitchPipelineController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('briefs', BriefController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('briefs/{brief}/analysis', [BriefController::class, 'updateAnalysis'])->name('briefs.analysis.update');
    Route::post('briefs/{brief}/analyze', [BriefController::class, 'analyze'])->name('briefs.analyze');
    Route::get('briefs/{brief}/preview', [BriefController::class, 'preview'])->name('briefs.preview');

    Route::post('services', [ServiceController::class, 'store'])->name('services.store');

    Route::post('pitch-assignments/{pitchAssignment}/accept', [PitchPipelineController::class, 'accept'])->name('pitch-assignments.accept');
    Route::post('pitch-assignments/{pitchAssignment}/decline', [PitchPipelineController::class, 'decline'])->name('pitch-assignments.decline');

    Route::resource('business-units', BusinessUnitController::class)->except(['show']);

    Route::resource('users', UserController::class);

    Route::get('pitch-pipeline', [PitchPipelineController::class, 'index'])->name('pitch-pipeline.index');

    Route::get('ai-usage', [AiUsageDashboardController::class, 'index'])->name('ai-usage');
});

require __DIR__.'/settings.php';

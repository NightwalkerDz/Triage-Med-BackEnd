<?php

use App\Http\Controllers\Api\Admin\SymptomController as AdminSymptomController;
use App\Http\Controllers\Api\Admin\UrgencyLevelController as AdminUrgencyLevelController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MedecinController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\SymptomController;
use App\Http\Controllers\Api\TriageController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/medecins', [MedecinController::class, 'index']);
    Route::post('/medecins', [MedecinController::class, 'store']);
    Route::put('/medecins/{medecin}', [MedecinController::class, 'update']);
    Route::delete('/medecins/{medecin}', [MedecinController::class, 'destroy']);

    Route::get('/symptoms', [SymptomController::class, 'index']);
    Route::post('/triage/evaluer', [TriageController::class, 'evaluer']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);

    Route::delete('/triage/{triage}', [TriageController::class, 'destroy']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/urgency-levels', [AdminUrgencyLevelController::class, 'index']);
        Route::post('/urgency-levels', [AdminUrgencyLevelController::class, 'store']);
        Route::put('/urgency-levels/{urgencyLevel}', [AdminUrgencyLevelController::class, 'update']);
        Route::delete('/urgency-levels/{urgencyLevel}', [AdminUrgencyLevelController::class, 'destroy']);

        Route::get('/symptoms', [AdminSymptomController::class, 'index']);
        Route::post('/symptoms', [AdminSymptomController::class, 'store']);
        Route::put('/symptoms/{symptom}', [AdminSymptomController::class, 'update']);
        Route::delete('/symptoms/{symptom}', [AdminSymptomController::class, 'destroy']);
    });
});

<?php

use App\Http\Controllers\CameraAngleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'savedAngles' => auth()->user()->cameraAngles()->latest()->get()
        ]);
    })->name('dashboard');

    // 構図保存・削除用ルーティング
    Route::post('/camera-angles', [CameraAngleController::class, 'store'])->name('camera-angles.store');
    Route::delete('/camera-angles/{cameraAngle}', [CameraAngleController::class, 'destroy'])->name('camera-angles.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TugasController;
use App\Models\Tugas;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {

    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect('/admin/dashboard');
    }

    return redirect('/peserta/dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified']);

Route::get('/peserta/dashboard', function () {
    return view('peserta.dashboard');
})->middleware(['auth', 'verified']);

Route::get('/peserta/upload', function () {
    return view('peserta.upload');
})->middleware('auth');

Route::get('/peserta/tugas', function () {

    $tugas = Tugas::where('user_id', auth()->id())->get();

    return view('peserta.tugas', compact('tugas'));

})->middleware('auth');

Route::post('/peserta/upload', [TugasController::class, 'store'])
    ->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/peserta/tugas/{id}/edit', [TugasController::class, 'edit'])
    ->name('peserta.tugas.edit');

    Route::put('/peserta/tugas/{id}', [TugasController::class, 'update'])
    ->name('peserta.tugas.update');

    Route::delete('/peserta/tugas/{id}', [TugasController::class, 'destroy'])
    ->name('peserta.tugas.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/tugas', [TugasController::class, 'indexAdmin'])
        ->name('admin.tugas.index');

    Route::put('/admin/tugas/{id}/status', [TugasController::class, 'updateStatus'])
    ->name('admin.tugas.updateStatus');

    Route::put('/admin/tugas/{id}/komentar', [TugasController::class, 'simpanKomentar'])
    ->name('admin.tugas.komentar');

    Route::get('/admin/tugas/{id}/download',
    [TugasController::class, 'download'])
    ->name('admin.tugas.download');

    Route::get(
    '/admin/tugas/{id}/analisis-ai',
    [TugasController::class, 'analisisAI']
)->name('admin.tugas.ai')->middleware('throttle:5,1');
});

require __DIR__.'/auth.php';
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YoutubeController;
use App\Http\Controllers\DownloadController;

// Только API-маршруты
Route::prefix('api')->group(function () {
    Route::post('/youtube/convert', [YoutubeController::class, 'download'])->name('youtube.download');
    Route::get('/download/status/{id}', [DownloadController::class, 'status'])->name('download.status');
    Route::get('/download/file/{id}', [DownloadController::class, 'file'])->name('download.file');
    Route::post('/download/cancel/{id}', [DownloadController::class, 'cancel'])->name('download.cancel');
    Route::post('/remove-video', [YoutubeController::class, 'removeVideo'])->name('youtube.remove_video');
    Route::post('/download/start', [DownloadController::class, 'start']);
});
Route::get('/download/file/{id}', [DownloadController::class, 'file'])->name('download.file');
// В самом конце — только SPA!
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

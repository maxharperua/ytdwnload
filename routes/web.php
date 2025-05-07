<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YoutubeController;
use App\Http\Controllers\DownloadController;

// Удаляем дублирующий маршрут
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [YoutubeController::class, 'index'])->name('youtube.index');
Route::post('/download', [YoutubeController::class, 'download'])->name('youtube.download');
Route::get('/download/{format}', [YoutubeController::class, 'downloadFormat'])->name('youtube.download.format');

// Запуск задачи на скачивание
Route::post('/download/start', [DownloadController::class, 'start'])->name('download.start');
// Страница прогресса
Route::get('/download/progress/{id}', [DownloadController::class, 'progress'])->name('download.progress');
// API: статус задачи
Route::get('/download/status/{id}', [DownloadController::class, 'status'])->name('download.status');
// Скачивание готового файла
Route::get('/download/file/{id}', [DownloadController::class, 'file'])->name('download.file');

// Отмена задачи генерации
Route::post('/download/cancel/{id}', [DownloadController::class, 'cancel'])->name('download.cancel');

<?php

namespace App\Http\Controllers;

use App\Models\DownloadTask;
use App\Jobs\DownloadVideoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    // Создание задачи и редирект на страницу прогресса
    public function start(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'format' => 'required|string',
        ]);
        $task = DownloadTask::create([
            'url' => $request->url,
            'format' => $request->format,
            'status' => 'pending',
            'progress' => 0,
        ]);
        DownloadVideoJob::dispatch($task->id);
        return redirect()->route('download.progress', ['id' => $task->id]);
    }

    // Страница прогресса
    public function progress($id)
    {
        $task = DownloadTask::findOrFail($id);
        return view('download.progress', compact('task'));
    }

    // API: статус задачи
    public function status($id)
    {
        $task = DownloadTask::findOrFail($id);
        return response()->json([
            'status' => $task->status,
            'progress' => $task->progress,
            'error' => $task->error,
            'download_url' => $task->status === 'finished' ? route('download.file', ['id' => $task->id]) : null,
        ]);
    }

    // Скачивание готового файла
    public function file(DownloadTask $task)
    {
        \Log::info('Attempting to download file', [
            'task_id' => $task->id,
            'file_path' => $task->file_path,
            'exists' => $task->file_path ? Storage::exists($task->file_path) : false,
            'status' => $task->status
        ]);

        if (!$task->file_path) {
            abort(404, 'Путь к файлу не указан');
        }

        if (!Storage::exists($task->file_path)) {
            abort(404, 'Файл не найден в storage: ' . $task->file_path);
        }

        return Storage::download($task->file_path);
    }

    // Отмена задачи
    public function cancel($id, Request $request)
    {
        $task = DownloadTask::findOrFail($id);
        $success = false;
        if (in_array($task->status, ['pending', 'processing'])) {
            if ($task->file_path && file_exists($task->file_path)) {
                @unlink($task->file_path);
            }
            $task->delete();
            $success = true;
        }
        return response()->json(['success' => $success]);
    }
} 
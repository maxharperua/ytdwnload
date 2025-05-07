<?php

namespace App\Http\Controllers;

use App\Models\DownloadTask;
use App\Jobs\DownloadVideoJob;
use Illuminate\Http\Request;

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
    public function file($id)
    {
        $task = DownloadTask::findOrFail($id);
        if ($task->status !== 'finished' || !$task->file_path || !file_exists($task->file_path)) {
            abort(404);
        }
        return response()->download($task->file_path, 'video.mp4')->deleteFileAfterSend(true);
    }

    // Отмена задачи
    public function cancel($id, Request $request)
    {
        $task = DownloadTask::findOrFail($id);
        if (in_array($task->status, ['pending', 'processing'])) {
            $task->status = 'cancelled';
            if ($task->file_path && file_exists($task->file_path)) {
                @unlink($task->file_path);
            }
            $task->save();
        }
        return redirect($request->input('redirect_to', '/'))->with('success', 'Задача отменена.');
    }
} 
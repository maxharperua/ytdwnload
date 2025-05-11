<?php

namespace App\Http\Controllers;

use App\Models\DownloadTask;
use App\Jobs\DownloadVideoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    // Создание задачи и редирект на страницу прогресса
    public function start(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'format' => 'required|string',
            'type' => 'required|in:video,audio'
        ]);
        $task = DownloadTask::create([
            'url' => $request->url,
            'format' => $request->format,
            'type' => $request->type,
            'status' => 'pending',
            'progress' => 0,
        ]);
        DownloadVideoJob::dispatch($task->id);

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json(['id' => $task->id]);
        }
        return redirect()->route('download.progress', ['id' => $task->id, 'type' => $request->type]);
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
        $downloadUrl = null;
        
        if ($task->status === 'finished' && $task->file_path) {
            $fullPath = storage_path('app/' . $task->file_path);
            Log::info('Checking file status', [
                'task_id' => $id,
                'file_path' => $task->file_path,
                'full_path' => $fullPath,
                'exists' => file_exists($fullPath),
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'perms' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : null
            ]);
            
            if (file_exists($fullPath) && filesize($fullPath) > 0) {
                $downloadUrl = route('download.file', ['id' => $task->id]);
            } else {
                // Если файл не найден или пустой, но статус finished - меняем статус на error
                $task->status = 'error';
                $task->error = 'Файл не найден или поврежден';
                $task->save();
                Log::error('File not found or empty', [
                    'task_id' => $id,
                    'file_path' => $task->file_path,
                    'full_path' => $fullPath
                ]);
            }
        }
        
        return response()->json([
            'status' => $task->status,
            'progress' => $task->progress,
            'error' => $task->error,
            'download_url' => $downloadUrl,
        ]);
    }

    // Скачивание готового файла
    public function file($id)
    {
        Log::info('Attempting to download file', ['task_id' => $id]);
        
        $task = DownloadTask::findOrFail($id);
        $fullPath = storage_path('app/' . $task->file_path);
        
        Log::info('Task found', [
            'task_id' => $id,
            'file_path' => $task->file_path,
            'status' => $task->status,
            'full_path' => $fullPath,
            'file_exists' => file_exists($fullPath),
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
            'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : null
        ]);

        if (!$task->file_path) {
            Log::error('File path is empty', ['task_id' => $id]);
            abort(404);
        }

        if (!file_exists($fullPath)) {
            Log::error('File does not exist', [
                'task_id' => $id,
                'file_path' => $task->file_path,
                'full_path' => $fullPath
            ]);
            abort(404);
        }

        if ($task->status !== 'finished') {
            Log::error('Task is not finished', [
                'task_id' => $id,
                'status' => $task->status
            ]);
            abort(404);
        }

        if (filesize($fullPath) === 0) {
            Log::error('File is empty', [
                'task_id' => $id,
                'file_path' => $task->file_path
            ]);
            abort(404);
        }

        Log::info('File found, preparing download', [
            'task_id' => $id,
            'file_path' => $task->file_path,
            'mime_type' => mime_content_type($fullPath)
        ]);

        return response()->download(
            $fullPath,
            basename($task->file_path),
            [],
            'inline'
        );
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
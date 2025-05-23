<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DownloadTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanOldDownloads extends Command
{
    protected $signature = 'downloads:clean';
    protected $description = 'Удаляет старые сгенерированные видео и задачи (старше 1 часа)';

    public function handle()
    {
        $threshold = Carbon::now()->subHour();
        
        // Удаляем старые задачи и их файлы
        $tasks = DownloadTask::whereIn('status', ['finished', 'error'])
            ->where('created_at', '<', $threshold)
            ->get();
            
        Log::info('Starting cleanup of old downloads', [
            'threshold' => $threshold,
            'tasks_count' => $tasks->count()
        ]);
        
        $deleted = 0;
        foreach ($tasks as $task) {
            if ($task->file_path) {
                $fullPath = storage_path('app/' . $task->file_path);
                Log::info('Checking file for deletion', [
                    'task_id' => $task->id,
                    'file_path' => $task->file_path,
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'created_at' => $task->created_at
                ]);
                
                if (file_exists($fullPath)) {
                    if (@unlink($fullPath)) {
                        Log::info('File deleted successfully', ['path' => $fullPath]);
                    } else {
                        Log::error('Failed to delete file', ['path' => $fullPath]);
                    }
                }
            }
            $task->delete();
            $deleted++;
        }
        $this->info("Удалено задач: $deleted");

        // Отменяем зависшие задачи
        $stuck = DownloadTask::whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<', Carbon::now()->subMinutes(15))
            ->get();
            
        Log::info('Checking for stuck tasks', ['count' => $stuck->count()]);
        
        foreach ($stuck as $task) {
            if ($task->file_path) {
                $fullPath = storage_path('app/' . $task->file_path);
                if (file_exists($fullPath)) {
                    if (@unlink($fullPath)) {
                        Log::info('Stuck task file deleted', ['path' => $fullPath]);
                    } else {
                        Log::error('Failed to delete stuck task file', ['path' => $fullPath]);
                    }
                }
            }
            $task->delete();
        }

        // Удаляем файлы, которые не имеют соответствующих записей в базе
        $downloadsDir = storage_path('app/downloads');
        if (is_dir($downloadsDir)) {
            $files = glob($downloadsDir . '/*.mp4');
            foreach ($files as $file) {
                $filename = basename($file);
                $exists = DownloadTask::where('file_path', 'downloads/' . $filename)->exists();
                
                if (!$exists) {
                    Log::info('Found orphaned file', [
                        'file' => $filename,
                        'path' => $file
                    ]);
                    
                    if (@unlink($file)) {
                        Log::info('Orphaned file deleted', ['path' => $file]);
                        $deleted++;
                    } else {
                        Log::error('Failed to delete orphaned file', ['path' => $file]);
                    }
                }
            }
        }
        
        $this->info("Всего удалено файлов: $deleted");
    }
} 
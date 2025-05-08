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
        $threshold = Carbon::now()->subMinute();
        $tasks = DownloadTask::whereIn('status', ['finished', 'error'])
            ->where('created_at', '<', $threshold)
            ->get();
        $deleted = 0;
        foreach ($tasks as $task) {
            if ($task->file_path && file_exists($task->file_path)) {
                @unlink($task->file_path);
            }
            $task->delete();
            $deleted++;
        }
        $this->info("Удалено задач: $deleted");

        // Отменяем зависшие задачи (например, старше 15 минут)
        $stuck = DownloadTask::whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<', Carbon::now()->subMinutes(15))
            ->get();
        foreach ($stuck as $task) {
            if ($task->file_path && file_exists($task->file_path)) {
                @unlink($task->file_path);
            }
            $task->delete();
        }
    }
} 
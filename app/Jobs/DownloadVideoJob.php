<?php

namespace App\Jobs;

use App\Models\DownloadTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle()
    {
        $task = DownloadTask::find($this->taskId);
        if (!$task) return;
        $task->status = 'processing';
        $task->progress = 5;
        $task->save();
        try {
            $videoUrl = $task->url;
            $format = $task->format;
            // Получаем информацию о видео
            $infoCommand = "yt-dlp -j " . escapeshellarg($videoUrl);
            $infoOutput = shell_exec($infoCommand);
            if (!$infoOutput) throw new \Exception('Не удалось получить информацию о видео');
            $videoInfo = json_decode($infoOutput, true);
            if (!isset($videoInfo['formats'])) throw new \Exception('Форматы видео не найдены');
            // Находим выбранный формат
            $selectedFormat = null;
            foreach ($videoInfo['formats'] as $f) {
                if ($f['format_id'] == $format) {
                    $selectedFormat = $f;
                    break;
                }
            }
            if (!$selectedFormat) throw new \Exception('Формат не найден');
            // Если формат содержит и видео, и аудио — просто скачиваем
            if (isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] !== 'none' && isset($selectedFormat['acodec']) && $selectedFormat['acodec'] !== 'none') {
                $tmpOutput = "/tmp/merged_" . uniqid() . ".mp4";
                $cmd = "yt-dlp -f " . escapeshellarg($format) . " -o " . escapeshellarg($tmpOutput) . " " . escapeshellarg($videoUrl);
                shell_exec($cmd);
                $task->progress = 80;
                $task->save();
            } else {
                // Если только видео — ищем лучший аудиоформат
                $bestAudio = null;
                foreach ($videoInfo['formats'] as $f) {
                    if (
                        isset($f['acodec']) && $f['acodec'] !== 'none' &&
                        isset($f['vcodec']) && $f['vcodec'] === 'none' &&
                        in_array($f['ext'], ['m4a', 'mp4']) &&
                        (!isset($f['protocol']) || $f['protocol'] !== 'm3u8')
                    ) {
                        if (!$bestAudio || (isset($f['abr']) && isset($bestAudio['abr']) && $f['abr'] > $bestAudio['abr'])) {
                            $bestAudio = $f;
                        }
                    }
                }
                if (!$bestAudio) throw new \Exception('Не удалось найти подходящий аудиоформат');
                $tmpVideo = "/tmp/video_" . uniqid() . ".mp4";
                $tmpAudio = "/tmp/audio_" . uniqid() . ".m4a";
                $tmpOutput = "/tmp/merged_" . uniqid() . ".mp4";
                shell_exec("yt-dlp -f " . escapeshellarg($selectedFormat['format_id']) . " -o " . escapeshellarg($tmpVideo) . " " . escapeshellarg($videoUrl));
                $task->progress = 40;
                $task->save();
                shell_exec("yt-dlp -f " . escapeshellarg($bestAudio['format_id']) . " -o " . escapeshellarg($tmpAudio) . " " . escapeshellarg($videoUrl));
                $task->progress = 60;
                $task->save();
                shell_exec("ffmpeg -y -i " . escapeshellarg($tmpVideo) . " -i " . escapeshellarg($tmpAudio) . " -c copy -map 0:v:0 -map 1:a:0 " . escapeshellarg($tmpOutput) . " 2>&1");
                @unlink($tmpVideo);
                @unlink($tmpAudio);
            }
            if (!file_exists($tmpOutput)) throw new \Exception('Не удалось создать итоговый файл');
            $task->file_path = $tmpOutput;
            $task->progress = 100;
            $task->status = 'finished';
            $task->save();
        } catch (\Exception $e) {
            $task->status = 'error';
            $task->error = $e->getMessage();
            $task->save();
            Log::error('DownloadVideoJob error', ['error' => $e->getMessage()]);
        }
    }
} 
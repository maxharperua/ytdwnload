<?php

namespace App\Jobs;

use App\Models\DownloadTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskId;
    public $tries = 3;
    public $timeout = 3600;

    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    public function handle()
    {
        Log::info('Starting DownloadVideoJob', ['taskId' => $this->taskId]);
        
        $task = DownloadTask::find($this->taskId);
        if (!$task) {
            Log::error('Task not found', ['taskId' => $this->taskId]);
            return;
        }
        
        $task->status = 'processing';
        $task->progress = 5;
        $task->save();
        
        try {
            $videoUrl = $task->url;
            $format = $task->format;
            
            Log::info('Getting video info', ['url' => $videoUrl, 'format' => $format]);
            
            // Получаем информацию о видео
            $infoCommand = "yt-dlp -j " . escapeshellarg($videoUrl);
            Log::info('Executing command', ['command' => $infoCommand]);
            
            $infoOutput = shell_exec($infoCommand);
            if (!$infoOutput) {
                Log::error('Failed to get video info', ['command' => $infoCommand]);
                throw new \Exception('Не удалось получить информацию о видео');
            }
            
            $videoInfo = json_decode($infoOutput, true);
            if (!isset($videoInfo['formats'])) {
                Log::error('No formats found in video info', ['info' => $videoInfo]);
                throw new \Exception('Форматы видео не найдены');
            }
            
            // Находим выбранный формат
            $selectedFormat = null;
            foreach ($videoInfo['formats'] as $f) {
                if ($f['format_id'] == $format) {
                    $selectedFormat = $f;
                    break;
                }
            }
            
            if (!$selectedFormat) {
                Log::error('Selected format not found', ['format' => $format, 'available_formats' => $videoInfo['formats']]);
                throw new \Exception('Формат не найден');
            }
            
            Log::info('Selected format found', ['format' => $selectedFormat]);
            
            // Создаем временную директорию в storage
            $tmpDir = storage_path('app/tmp');
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }
            
            // Если формат содержит и видео, и аудио — просто скачиваем
            if (isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] !== 'none' && 
                isset($selectedFormat['acodec']) && $selectedFormat['acodec'] !== 'none') {
                
                $tmpOutput = $tmpDir . '/merged_' . uniqid() . '.mp4';
                $cmd = "yt-dlp -f " . escapeshellarg($format) . " -o " . escapeshellarg($tmpOutput) . " " . 
                       escapeshellarg($videoUrl) . " --no-warnings --no-playlist --no-check-certificate " .
                       "--progress-template '%(progress._percent_str)s' --newline";
                
                Log::info('Downloading video with audio', ['command' => $cmd]);
                
                $process = popen($cmd . " 2>&1", "r");
                if (!$process) {
                    throw new \Exception('Не удалось запустить процесс скачивания');
                }
                
                while (!feof($process)) {
                    $line = fgets($process);
                    if ($line !== false) {
                        Log::info('Download progress', ['output' => trim($line)]);
                        if (preg_match('/(\d+\.\d+)%/', $line, $matches)) {
                            $progress = (int)$matches[1];
                            $task->progress = $progress;
                            $task->save();
                        }
                    }
                }
                pclose($process);
                
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
                
                if (!$bestAudio) {
                    Log::error('No suitable audio format found');
                    throw new \Exception('Не удалось найти подходящий аудиоформат');
                }
                
                Log::info('Best audio format found', ['format' => $bestAudio]);
                
                $tmpVideo = $tmpDir . '/video_' . uniqid() . '.mp4';
                $tmpAudio = $tmpDir . '/audio_' . uniqid() . '.m4a';
                $tmpOutput = $tmpDir . '/merged_' . uniqid() . '.mp4';
                
                // Скачиваем видео
                $videoCmd = "yt-dlp -f " . escapeshellarg($selectedFormat['format_id']) . " -o " . 
                           escapeshellarg($tmpVideo) . " " . escapeshellarg($videoUrl) . 
                           " --no-warnings --no-playlist --no-check-certificate " .
                           "--progress-template '%(progress._percent_str)s' --newline";
                
                Log::info('Downloading video', ['command' => $videoCmd]);
                
                $process = popen($videoCmd . " 2>&1", "r");
                if (!$process) {
                    throw new \Exception('Не удалось запустить процесс скачивания видео');
                }
                
                while (!feof($process)) {
                    $line = fgets($process);
                    if ($line !== false) {
                        Log::info('Video download progress', ['output' => trim($line)]);
                        if (preg_match('/(\d+\.\d+)%/', $line, $matches)) {
                            $progress = (int)($matches[1] * 0.4); // 40% от общего прогресса
                            $task->progress = $progress;
                            $task->save();
                        }
                    }
                }
                pclose($process);
                
                // Скачиваем аудио
                $audioCmd = "yt-dlp -f " . escapeshellarg($bestAudio['format_id']) . " -o " . 
                           escapeshellarg($tmpAudio) . " " . escapeshellarg($videoUrl) . 
                           " --no-warnings --no-playlist --no-check-certificate " .
                           "--progress-template '%(progress._percent_str)s' --newline";
                
                Log::info('Downloading audio', ['command' => $audioCmd]);
                
                $process = popen($audioCmd . " 2>&1", "r");
                if (!$process) {
                    throw new \Exception('Не удалось запустить процесс скачивания аудио');
                }
                
                while (!feof($process)) {
                    $line = fgets($process);
                    if ($line !== false) {
                        Log::info('Audio download progress', ['output' => trim($line)]);
                        if (preg_match('/(\d+\.\d+)%/', $line, $matches)) {
                            $progress = 40 + (int)($matches[1] * 0.2); // 20% от общего прогресса
                            $task->progress = $progress;
                            $task->save();
                        }
                    }
                }
                pclose($process);
                
                // Объединяем видео и аудио
                $mergeCmd = "ffmpeg -y -i " . escapeshellarg($tmpVideo) . " -i " . 
                           escapeshellarg($tmpAudio) . " -c copy -map 0:v:0 -map 1:a:0 " . 
                           escapeshellarg($tmpOutput) . " 2>&1";
                
                Log::info('Merging video and audio', ['command' => $mergeCmd]);
                $mergeOutput = shell_exec($mergeCmd);
                Log::info('Merge output', ['output' => $mergeOutput]);
                
                @unlink($tmpVideo);
                @unlink($tmpAudio);
            }
            
            if (!file_exists($tmpOutput)) {
                Log::error('Final file not created', ['path' => $tmpOutput]);
                throw new \Exception('Не удалось создать итоговый файл');
            }
            
            // Перемещаем файл в постоянное хранилище
            $finalPath = 'downloads/' . basename($tmpOutput);
            Log::info('Saving file to storage', [
                'tmpPath' => $tmpOutput,
                'finalPath' => $finalPath,
                'exists' => file_exists($tmpOutput),
                'size' => file_exists($tmpOutput) ? filesize($tmpOutput) : 0,
                'storage_path' => storage_path('app/' . $finalPath),
                'storage_exists' => Storage::exists($finalPath)
            ]);
            
            if (!file_exists($tmpOutput)) {
                throw new \Exception('Временный файл не найден: ' . $tmpOutput);
            }
            
            $content = file_get_contents($tmpOutput);
            if ($content === false) {
                throw new \Exception('Не удалось прочитать временный файл');
            }
            
            // Проверяем, существует ли директория
            $dirPath = storage_path('app/downloads');
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            
            // Сохраняем файл напрямую
            $fullPath = storage_path('app/' . $finalPath);
            if (file_put_contents($fullPath, $content) === false) {
                throw new \Exception('Не удалось сохранить файл: ' . $fullPath);
            }
            
            Log::info('File saved directly', [
                'path' => $fullPath,
                'exists' => file_exists($fullPath),
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'permissions' => file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : null
            ]);
            
            @unlink($tmpOutput);
            
            $task->file_path = $finalPath;
            $task->progress = 100;
            $task->status = 'finished';
            $task->save();
            
            Log::info('Download completed successfully', ['taskId' => $this->taskId]);
            
        } catch (\Exception $e) {
            Log::error('DownloadVideoJob error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'taskId' => $this->taskId
            ]);
            
            $task->status = 'error';
            $task->error = $e->getMessage();
            $task->save();
        }
    }
} 
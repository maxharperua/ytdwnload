<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\DownloadTask;

class YoutubeController extends Controller
{
    public function index()
    {
        $videoUrl = session('video_url');
        if ($videoUrl) {
            $request = new Request();
            $request->merge(['url' => $videoUrl]);
            $request->headers->set('X-CSRF-TOKEN', csrf_token());
            return $this->download($request);
        }
        return view('youtube.index', ['videoUrl' => $videoUrl]);
    }

    public function download(Request $request)
    {
        Log::info('Download method called', ['url' => $request->url]);
        
        $request->validate([
            'url' => 'required|url'
        ]);

        Log::info('Validation passed');

        try {
            // Сохраняем URL в сессию
            session(['video_url' => $request->url]);
            Log::info('URL saved to session');

            // Получаем информацию о видео через yt-dlp
            $command = "yt-dlp -j " . escapeshellarg($request->url);
            $output = shell_exec($command);
            
            if (!$output) {
                throw new \Exception('Не удалось получить информацию о видео');
            }

            $videoInfo = json_decode($output, true);
            Log::info('Video info received', ['info' => $videoInfo]);

            if (!isset($videoInfo['formats'])) {
                throw new \Exception('Форматы видео не найдены');
            }

            $muxed = [];
            $video_only = [];
            $audio_only = [];
            foreach ($videoInfo['formats'] as $format) {
                // Пропускаем m3u8-форматы
                if (
                    (isset($format['ext']) && $format['ext'] === 'm3u8') ||
                    (isset($format['protocol']) && $format['protocol'] === 'm3u8')
                ) {
                    continue;
                }
                // Определяем качество
                $quality = $format['format_note'] ?? ($format['height'] ? $format['height'] . 'p' : null);
                if (!$quality) continue;
                // Muxed: mp4, есть видео и аудио, качество >= 480p
                if (
                    isset($format['vcodec']) && $format['vcodec'] !== 'none' &&
                    isset($format['acodec']) && $format['acodec'] !== 'none' &&
                    $format['ext'] === 'mp4' &&
                    (
                        (isset($format['height']) && $format['height'] >= 480) ||
                        (isset($format['format_note']) && preg_match('/(\d+)p/', $format['format_note'], $matches) && intval($matches[1]) >= 480)
                    )
                ) {
                    // Сохраняем только лучший muxed для каждого качества (по битрейту или первому)
                    if (!isset($muxed[$quality]) || (isset($format['tbr']) && $format['tbr'] > ($muxed[$quality]['tbr'] ?? 0))) {
                        $muxed[$quality] = [
                            'itag' => $format['format_id'],
                            'quality' => $quality,
                            'mimeType' => $format['ext'],
                            'url' => $format['url'],
                            'label' => 'Видео + звук',
                            'tbr' => $format['tbr'] ?? 0
                        ];
                    }
                    // После формирования $item для muxed:
                    // Проверяем активную задачу
                    $activeTask = DownloadTask::where('url', $request->url)
                        ->where('format', $format['format_id'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->orderByDesc('created_at')
                        ->first();
                    if ($activeTask) {
                        $muxed[$quality]['active_task_id'] = $activeTask->id;
                    }
                }
                // Только видео: mp4, есть видео, нет аудио, качество >= 480p
                elseif (
                    isset($format['vcodec']) && $format['vcodec'] !== 'none' &&
                    isset($format['acodec']) && $format['acodec'] === 'none' &&
                    $format['ext'] === 'mp4' &&
                    (
                        (isset($format['height']) && $format['height'] >= 480) ||
                        (isset($format['format_note']) && preg_match('/(\d+)p/', $format['format_note'], $matches) && intval($matches[1]) >= 480)
                    )
                ) {
                    // Сохраняем только лучший video-only для каждого качества (по битрейту или первому)
                    if (!isset($video_only[$quality]) || (isset($format['tbr']) && $format['tbr'] > ($video_only[$quality]['tbr'] ?? 0))) {
                        $video_only[$quality] = [
                            'itag' => $format['format_id'],
                            'quality' => $quality,
                            'mimeType' => $format['ext'],
                            'url' => $format['url'],
                            'tbr' => $format['tbr'] ?? 0
                        ];
                    }
                    // После формирования $item для video_only:
                    // Проверяем активную задачу
                    $activeTask = DownloadTask::where('url', $request->url)
                        ->where('format', $format['format_id'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->orderByDesc('created_at')
                        ->first();
                    if ($activeTask) {
                        $video_only[$quality]['active_task_id'] = $activeTask->id;
                    }
                }
                // Только аудио: m4a или mp4, есть аудио, нет видео
                elseif (
                    isset($format['acodec']) && $format['acodec'] !== 'none' &&
                    isset($format['vcodec']) && $format['vcodec'] === 'none' &&
                    in_array($format['ext'], ['m4a', 'mp4'])
                ) {
                    // Для аудио можно оставить все варианты
                    $audio_only[] = [
                        'itag' => $format['format_id'],
                        'quality' => $format['format_note'] ?? ($format['abr'] ?? '') . ' аудио',
                        'mimeType' => $format['ext'],
                        'url' => $format['url'],
                        'label' => 'Только звук',
                        'tbr' => $format['tbr'] ?? 0
                    ];
                }
            }

            // Собираем финальный список: приоритет muxed, если нет — video_only
            $formats = [];
            foreach ($muxed as $q => $item) {
                $formats[] = $item;
            }
            foreach ($video_only as $q => $item) {
                if (!isset($muxed[$q])) {
                    $formats[] = $item;
                }
            }
            // Аудио-форматы можно добавить в конец, если нужно
            // foreach ($audio_only as $item) {
            //     $formats[] = $item;
            // }

            $thumbnail = $videoInfo['thumbnail'] ?? null;

            if (empty($formats)) {
                return back()->with('error', 'Не найдены доступные форматы видео');
            }

            return view('youtube.index', compact('formats', 'thumbnail'));
        } catch (\Exception $e) {
            Log::error('Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function downloadFormat($format)
    {
        try {
            $videoUrl = session('video_url');
            if (!$videoUrl) {
                return back()->with('error', 'Ссылка на видео не найдена. Пожалуйста, введите ссылку снова.');
            }

            // Получаем информацию о видео
            $infoCommand = "yt-dlp -j " . escapeshellarg($videoUrl);
            $infoOutput = shell_exec($infoCommand);
            if (!$infoOutput) {
                throw new \Exception('Не удалось получить информацию о видео');
            }
            $videoInfo = json_decode($infoOutput, true);
            if (!isset($videoInfo['formats'])) {
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
                throw new \Exception('Формат не найден');
            }

            // Если формат содержит и видео, и аудио — просто редирект
            if (isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] !== 'none' && isset($selectedFormat['acodec']) && $selectedFormat['acodec'] !== 'none') {
                $command = "yt-dlp -f " . escapeshellarg($format) . " -g " . escapeshellarg($videoUrl);
                $url = trim(shell_exec($command));
                if (!$url) {
                    throw new \Exception('Не удалось получить ссылку на видео');
                }
                return redirect($url);
            }

            // Если только видео — ищем лучший аудиоформат
            if (isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] !== 'none' && isset($selectedFormat['acodec']) && $selectedFormat['acodec'] === 'none') {
                // Находим лучший аудиоформат (m4a, mp4, максимальный битрейт)
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
                    throw new \Exception('Не удалось найти подходящий аудиоформат');
                }

                // Скачиваем видео и аудио во временные файлы
                $tmpVideo = "/tmp/video_" . uniqid() . ".mp4";
                $tmpAudio = "/tmp/audio_" . uniqid() . ".m4a";
                $tmpOutput = "/tmp/merged_" . uniqid() . ".mp4";

                $videoCmd = "yt-dlp -f " . escapeshellarg($selectedFormat['format_id']) . " -o " . escapeshellarg($tmpVideo) . " " . escapeshellarg($videoUrl);
                $audioCmd = "yt-dlp -f " . escapeshellarg($bestAudio['format_id']) . " -o " . escapeshellarg($tmpAudio) . " " . escapeshellarg($videoUrl);
                shell_exec($videoCmd);
                shell_exec($audioCmd);

                // Объединяем через ffmpeg
                $ffmpegCmd = "ffmpeg -y -i " . escapeshellarg($tmpVideo) . " -i " . escapeshellarg($tmpAudio) . " -c copy -map 0:v:0 -map 1:a:0 " . escapeshellarg($tmpOutput) . " 2>&1";
                shell_exec($ffmpegCmd);

                // Отправляем файл пользователю
                if (!file_exists($tmpOutput)) {
                    // Удаляем временные файлы
                    @unlink($tmpVideo);
                    @unlink($tmpAudio);
                    throw new \Exception('Не удалось объединить видео и аудио');
                }
                return response()->download($tmpOutput, 'video.mp4')->deleteFileAfterSend(true);
            }

            // Если выбран только аудиоформат — просто редирект
            if (isset($selectedFormat['acodec']) && $selectedFormat['acodec'] !== 'none' && isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] === 'none') {
                $command = "yt-dlp -f " . escapeshellarg($format) . " -g " . escapeshellarg($videoUrl);
                $url = trim(shell_exec($command));
                if (!$url) {
                    throw new \Exception('Не удалось получить ссылку на аудио');
                }
                return redirect($url);
            }

            throw new \Exception('Неизвестный тип формата');
        } catch (\Exception $e) {
            Log::error('Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
}

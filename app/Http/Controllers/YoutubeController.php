<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\DownloadTask;
use App\Rules\VideoUrl;

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
            'url' => ['required', 'url', new VideoUrl]
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
                    // Сохраняем только лучший аудиоформат (по битрейту)
                    if (!isset($audio_only) || (isset($format['abr']) && $format['abr'] > ($audio_only['abr'] ?? 0))) {
                        $audio_only = [
                            'itag' => $format['format_id'],
                            'quality' => 'Аудио',
                            'mimeType' => 'mp3', // Изменяем на mp3
                            'url' => $format['url'],
                            'abr' => $format['abr'] ?? 0,
                            'label' => 'Только звук'
                        ];
                    }
                    // Проверяем активную задачу
                    $activeTask = DownloadTask::where('url', $request->url)
                        ->where('format', $format['format_id'])
                        ->whereIn('status', ['pending', 'processing'])
                        ->orderByDesc('created_at')
                        ->first();
                    if ($activeTask) {
                        $audio_only['active_task_id'] = $activeTask->id;
                    }
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
            // Добавляем аудио-формат MP3 в конец списка, если найден
            if (!empty($audio_only)) {
                $formats[] = $audio_only;
            }

            $thumbnail = $videoInfo['thumbnail'] ?? null;

            if (empty($formats)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Не найдены доступные форматы видео',
                    ], 422);
                }
                return back()->with('error', 'Не найдены доступные форматы видео');
            }

            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'formats' => $formats,
                    'thumbnail' => $thumbnail,
                ]);
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

            // Если выбран только аудиоформат — конвертируем в MP3
            if (isset($selectedFormat['acodec']) && $selectedFormat['acodec'] !== 'none' && isset($selectedFormat['vcodec']) && $selectedFormat['vcodec'] === 'none') {
                // Создаем временные файлы
                $tmpAudio = "/tmp/audio_" . uniqid() . ".m4a";
                $tmpOutput = "/tmp/audio_" . uniqid() . ".mp3";

                // Скачиваем аудио
                $audioCmd = "yt-dlp -f " . escapeshellarg($format) . " -o " . escapeshellarg($tmpAudio) . " " . escapeshellarg($videoUrl);
                shell_exec($audioCmd);

                // Конвертируем в MP3
                $ffmpegCmd = "ffmpeg -y -i " . escapeshellarg($tmpAudio) . " -vn -ar 44100 -ac 2 -b:a 192k " . escapeshellarg($tmpOutput) . " 2>&1";
                shell_exec($ffmpegCmd);

                // Проверяем результат конвертации
                if (!file_exists($tmpOutput) || filesize($tmpOutput) === 0) {
                    // Удаляем временные файлы
                    @unlink($tmpAudio);
                    throw new \Exception('Не удалось конвертировать аудио в MP3');
                }

                // Удаляем временный файл
                @unlink($tmpAudio);

                return response()->download($tmpOutput, 'audio.mp3')->deleteFileAfterSend(true);
            }

            throw new \Exception('Неизвестный тип формата');
        } catch (\Exception $e) {
            Log::error('Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function removeVideo(Request $request)
    {
        $request->session()->forget('video_url');
        return redirect()->route('youtube.index');
    }
}

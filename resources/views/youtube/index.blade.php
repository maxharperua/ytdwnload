<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Скачать видео и музыку с YouTube, RuTube, VK Видео, Shorts — YouTube Video Downloader</title>
    <meta name="description" content="Скачивайте видео и музыку с YouTube, RuTube, VK Видео, Shorts бесплатно и без регистрации. Быстрое скачивание в формате mp4 и mp3. Онлайн загрузчик — просто вставьте ссылку!">
    <meta name="keywords" content="скачать видео, скачать музыку, youtube downloader, rutube, vk видео, shorts, загрузчик ютуб, скачать с ютуба, скачать с rutube, скачать с вк, скачать shorts, video download, mp4, mp3, онлайн, бесплатно">
    <meta property="og:title" content="Скачать видео и музыку с YouTube, RuTube, VK Видео, Shorts — YouTube Video Downloader">
    <meta property="og:description" content="Быстрое и бесплатное скачивание видео и музыки с YouTube, RuTube, VK Видео, Shorts. Поддержка форматов mp4 и mp3.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ytload.ru/">
    <meta property="og:image" content="/preview.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Скачать видео и музыку с YouTube, RuTube, VK Видео, Shorts — YouTube Video Downloader">
    <meta name="twitter:description" content="Скачивайте видео и музыку с YouTube, RuTube, VK Видео, Shorts онлайн, быстро и бесплатно.">
    <meta name="twitter:image" content="/preview.png">
    <link rel="canonical" href="https://ytload.ru/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <style>
        body {
            background: linear-gradient(135deg, #282a36 0%, #44475a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', 'Arial', sans-serif;
            color: #f8f8f2;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background: rgba(40, 42, 54, 0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.45), 0 1.5px 8px 0 #bd93f9;
            border: 1.5px solid #44475a;
        }
        .form-control {
            margin-bottom: 1rem;
            background: #44475a;
            color: #f8f8f2;
            border: 1.5px solid #6272a4;
            border-radius: 8px;
            transition: box-shadow 0.3s, border 0.3s;
            box-shadow: 0 2px 8px rgba(139, 233, 253, 0.08);
        }
        .form-control:focus {
            border-color: #bd93f9;
            box-shadow: 0 0 0 2px #bd93f9;
            background: #44475a;
            color: #f8f8f2;
        }
        .btn-primary {
            width: 100%;
            background: linear-gradient(90deg, #bd93f9 0%, #ff79c6 100%);
            border: none;
            color: #282a36;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 2px 8px #bd93f9a0;
            transition: transform 0.15s, box-shadow 0.3s, background 0.3s;
            margin-bottom: 1.5rem;
        }
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(90deg, #ff79c6 0%, #bd93f9 100%);
            color: #282a36;
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 4px 16px #ff79c6a0;
        }
        .format-block {
            background: #44475a !important;
            box-shadow: 0 2px 12px 0 #282a3640, 0 1.5px 8px 0 #bd93f9;
            border: 1.5px solid #6272a4;
            transition: box-shadow 0.3s, transform 0.2s;
        }
        .format-block:hover {
            box-shadow: 0 6px 24px 0 #bd93f9a0, 0 1.5px 8px 0 #ff79c6;
            transform: translateY(-2px) scale(1.01);
        }
        .btn-success {
            background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
            color: #282a36;
            border: none;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 8px;
            min-width: 120px;
            transition: background 0.3s, transform 0.15s, box-shadow 0.3s;
            box-shadow: 0 2px 8px #50fa7b80;
        }
        .btn-success:hover, .btn-success:focus {
            background: linear-gradient(90deg, #8be9fd 0%, #50fa7b 100%);
            color: #282a36;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px #8be9fd80;
        }
        .badge.bg-secondary {
            background: linear-gradient(90deg, #6272a4 0%, #bd93f9 100%) !important;
            color: #f8f8f2;
            font-size: 1.1rem;
            padding: 0.5em 1em;
            border-radius: 6px;
            box-shadow: 0 1px 4px #bd93f980;
        }
        .img-fluid.rounded.shadow {
            border: 2px solid #bd93f9;
            margin-bottom: 1rem;
            background: #44475a;
            box-shadow: 0 4px 24px #282a36a0, 0 1.5px 8px 0 #bd93f9;
            transition: box-shadow 0.3s, transform 0.2s;
        }
        .img-fluid.rounded.shadow:hover {
            box-shadow: 0 8px 32px #ff79c6a0, 0 1.5px 8px 0 #8be9fd;
            transform: scale(1.02);
        }
        .btn-warning.disabled-link {
            pointer-events: auto;
            color: #282a36;
            background: linear-gradient(90deg, #f1fa8c 0%, #ffb86c 100%);
            border: none;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 8px;
            transition: background 0.3s, transform 0.15s, box-shadow 0.3s;
            box-shadow: 0 2px 8px #f1fa8c80;
        }
        .btn-warning.disabled-link:hover {
            background: linear-gradient(90deg, #ffb86c 0%, #f1fa8c 100%);
            color: #282a36;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px #ffb86c80;
        }
        .btn-outline-danger {
            min-width: 100px;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 8px;
            border: 1.5px solid #ff5555;
            color: #ff5555;
            background: transparent;
            transition: background 0.3s, color 0.3s, transform 0.15s, box-shadow 0.3s;
        }
        .btn-outline-danger:hover, .btn-outline-danger:focus {
            background: #ff5555;
            color: #282a36;
            box-shadow: 0 4px 16px #ff555580;
            transform: translateY(-2px) scale(1.04);
        }
        .d-flex.gap-2 > * { margin-right: 0.5rem; }
        .d-flex.gap-2 > *:last-child { margin-right: 0; }
        h1, h3 {
            color: #bd93f9;
            text-shadow: 0 2px 8px #282a36a0;
        }
        .text-info {
            color: #8be9fd !important;
        }
        .text-muted {
            color: #6272a4 !important;
        }
        .alert-danger {
            background: #ff5555;
            color: #f8f8f2;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px #ff555580;
        }
        .alert-success {
            background: #50fa7b;
            color: #282a36;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px #50fa7b80;
        }
        .text-danger {
            color: #ff5555 !important;
        }
        /* Плавные переходы для всех интерактивных элементов */
        button, .btn, .form-control, .format-block, .img-fluid.rounded.shadow {
            transition: all 0.3s cubic-bezier(.4,2,.3,1);
        }
        .remove-video-btn:hover, .remove-video-btn:focus {
            background:linear-gradient(90deg,#ff79c6 0%,#ff5555 100%) !important;
            transform:scale(1.12) rotate(12deg);
            box-shadow:0 4px 16px #ff79c6a0;
        }
        .remove-video-btn-animated {
            background: linear-gradient(90deg,#ff5555 0%,#ff79c6 100%);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px #ff79c6b0, 0 0 4px #ff79c6a0;
            transition: box-shadow 0.3s, transform 0.25s cubic-bezier(.4,2,.3,1), background 0.3s;
            position: relative;
            outline: none;
            filter: drop-shadow(0 0 4px #ff79c6a0);
        }
        .remove-video-btn-animated .remove-x {
            line-height: 1;
            color: #fff;
            font-weight: bold;
            transition: transform 0.3s cubic-bezier(.4,2,.3,1), text-shadow 0.3s;
            text-shadow: 0 0 4px #ff79c6, 0 0 1px #fff;
        }
        .remove-video-btn-animated:hover, .remove-video-btn-animated:focus {
            background: linear-gradient(90deg,#ff79c6 0%,#ff5555 100%) !important;
            box-shadow: 0 0 16px #ff79c6, 0 0 8px #ff5555;
            transform: scale(1.12) rotate(-12deg);
            animation: pulse-glow 0.7s infinite alternate;
        }
        .remove-video-btn-animated:hover .remove-x, .remove-video-btn-animated:focus .remove-x {
            transform: rotate(180deg) scale(1.12);
            text-shadow: 0 0 8px #fff, 0 0 4px #ff79c6;
        }
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 8px #ff79c6b0, 0 0 4px #ff79c6a0; }
            100% { box-shadow: 0 0 16px #ff79c6, 0 0 12px #ff5555; }
        }
        .preview-wrapper {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            position: relative;
            width: 100%;
            min-height: 240px;
        }
        @media (max-width: 600px) {
            .container {
                max-width: 98vw;
                padding: 1rem;
                border-radius: 10px;
            }
            h1, h3 {
                font-size: 1.3rem;
            }
            .form-control {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }
            .btn-primary, .btn-success, .btn-warning, .btn-outline-danger, .btn-outline-secondary {
                font-size: 1rem;
                min-width: unset;
                padding: 0.5rem 0.75rem;
                border-radius: 7px;
            }
            .preview-wrapper {
                min-height: 120px;
            }
            .img-fluid.rounded.shadow {
                max-width: 100%;
                max-height: 140px;
            }
            .remove-video-btn-animated {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
            }
            .remove-video-btn-animated .remove-x {
                font-size: 1rem !important;
            }
            .badge.bg-secondary {
                font-size: 0.95rem;
                padding: 0.3em 0.7em;
            }
            .format-block {
                font-size: 0.95rem;
                padding: 0.5rem 0.7rem;
            }
            .list-group {
                gap: 0.5rem;
            }
        }
        .preloader-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(40,42,54,0.85);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s;
        }
        .dracula-spinner {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 6px solid #bd93f9;
            border-top: 6px solid #8be9fd;
            border-right: 6px solid #ff79c6;
            border-bottom: 6px solid #50fa7b;
            border-left: 6px solid #44475a;
            animation: dracula-spin 1s linear infinite;
            box-shadow: 0 0 32px #bd93f9, 0 0 16px #ff79c6;
        }
        @keyframes dracula-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .disclaimer-block-fixed { display: none; }
        .disclaimer-block-below {
            max-width: 520px;
            margin: 2.2rem auto 1.2rem auto;
            font-size: 0.97rem;
            color: #aaa;
            opacity: 0.93;
            background: rgba(40,42,54,0.97);
            border-radius: 14px;
            box-shadow: 0 2px 16px #282a36a0;
            padding: 0.7rem 1.2rem 0.7rem 1.2rem;
        }
        @media (max-width: 600px) {
            .disclaimer-block-below {
                font-size: 0.89rem;
                padding: 0.5rem 0.5rem 0.5rem 0.5rem;
                max-width: 98vw;
                border-radius: 8px;
                margin: 1.2rem auto 0.7rem auto;
            }
        }
    </style>
    <!-- Yandex.RTB -->
    <script>window.yaContextCb=window.yaContextCb||[]</script>
    <script src="https://yandex.ru/ads/system/context.js" async></script>
</head>
<body>
    <div class="preloader-overlay" id="preloader" style="display:none;">
        <div class="dracula-spinner"></div>
    </div>
    <div class="container">
        <h1 class="text-center mb-4">YouTube Video Downloader</h1>
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="search-form" action="{{ route('youtube.download') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="url" 
                       name="url" 
                       class="form-control" 
                       placeholder="Введите ссылку на YouTube видео" 
                       required
                       value="{{ old('url') ?? (isset($videoUrl) ? $videoUrl : '') }}">
                @error('url')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Скачать</button>
        </form>

        @if(isset($thumbnail) && $thumbnail)
            <div class="preview-wrapper d-flex justify-content-center align-items-center mb-3 position-relative">
                <img src="{{ $thumbnail }}" alt="Превью видео" class="img-fluid rounded shadow" style="max-height:220px;object-fit:cover;">
                <form method="POST" action="{{ route('youtube.remove_video') }}" style="position:absolute;top:0;right:-2px;z-index:2;">
                    @csrf
                    <button type="submit" class="btn btn-danger remove-video-btn-animated" title="Удалить видео" style="width:28px;height:28px;min-width:28px;min-height:28px;padding:0;">
                        <span class="remove-x" style="font-size:1.2rem;">&times;</span>
                    </button>
                </form>
            </div>
        @endif

        @if(isset($formats))
            <div class="mt-4">
                <h3>Доступные форматы:</h3>
                <div class="list-group" id="formats-list">
                    @foreach($formats as $format)
                        <div class="format-block d-flex justify-content-between align-items-center mb-2 p-2 rounded shadow-sm" style="background:#f4f6fb;">
                            <div>
                                <span class="badge bg-secondary me-2" style="font-size:1rem;">{{ $format['quality'] }}</span>
                                <span class="text-muted" style="font-size:0.95rem;">{{ $format['mimeType'] }}</span>
                                @if(isset($format['label']))
                                    <span class="ms-2 small text-info">{{ $format['label'] }}</span>
                                @endif
                            </div>
                            @if(isset($format['active_task_id']))
                                <div class="d-flex gap-2">
                                    <a href="{{ route('download.progress', ['id' => $format['active_task_id']]) }}" class="btn btn-warning disabled-link">
                                        Генерация идёт...
                                    </a>
                                    <button type="button" class="btn btn-outline-danger cancel-btn"
                                        data-cancel-url="{{ route('download.cancel', ['id' => $format['active_task_id']]) }}">
                                        Отменить
                                    </button>
                                </div>
                            @else
                                <form class="download-form" method="POST" action="{{ route('download.start') }}">
                                    @csrf
                                    <input type="hidden" name="url" value="{{ request('url') ?? old('url') ?? (isset($videoUrl) ? $videoUrl : '') }}">
                                    <input type="hidden" name="format" value="{{ $format['itag'] }}">
                                    <button type="submit" class="btn btn-success">Скачать</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="disclaimer-block-below text-center">
            <p>
                Сервис ytload.ru предоставляет техническую возможность скачивания видео и музыки исключительно для личного некоммерческого использования. Администрация не несёт ответственности за дальнейшее использование скачанных файлов. Пользователь самостоятельно несёт ответственность за соблюдение авторских прав и законодательства своей страны.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Перехват формы, чтобы не терять url при переходе
        document.querySelectorAll('.format-download-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                let urlInput = document.querySelector('input[name="url"]');
                if (urlInput && !form.querySelector('input[name="url"]').value) {
                    form.querySelector('input[name="url"]').value = urlInput.value;
                }
            });
        });

        // AJAX отмена генерации
        document.querySelectorAll('.cancel-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Отменить генерацию этого видео?')) return;
                fetch(btn.dataset.cancelUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Можно перерисовать только этот блок, но проще:
                        location.reload();
                    }
                });
            });
        });

        // Прелоадер при отправке формы поиска
        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        }
        // Прелоадер при удалении видео (крестик)
        document.querySelectorAll('form[action*="remove-video"]').forEach(function(form) {
            form.addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
        // Прелоадер при скачивании формата (переход на генерацию)
        document.querySelectorAll('.download-form').forEach(function(form) {
            form.addEventListener('submit', function() {
                document.getElementById('preloader').style.display = 'flex';
            });
        });
        // Прелоадер при возврате к выбору видео
        // (ищем формы с кнопкой 'Назад к выбору видео')
        document.querySelectorAll('form').forEach(function(form) {
            if (form.innerText.includes('Назад к выбору видео')) {
                form.addEventListener('submit', function() {
                    document.getElementById('preloader').style.display = 'flex';
                });
            }
        });
    </script>
    <!-- Yandex.RTB R-A-15418559-1 -->
    <div id="yandex_rtb_R-A-15418559-1"></div>
    <script>
    window.yaContextCb.push(() => {
        Ya.Context.AdvManager.render({
            "blockId": "R-A-15418559-1",
            "renderTo": "yandex_rtb_R-A-15418559-1"
        })
    })
    </script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
    
        ym(101710991, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/101710991" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
 <!-- /Yandex.Metrika counter -->
</body>
</html> 
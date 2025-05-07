<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Video Downloader</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-control {
            margin-bottom: 1rem;
        }
        .btn-primary {
            width: 100%;
        }
        .format-download-form:hover {
            background: #e9ecef !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .btn-success {
            min-width: 120px;
            font-weight: 500;
            font-size: 1.05rem;
        }
        .badge.bg-secondary {
            background: #6c757d !important;
            font-size: 1.1rem;
            padding: 0.5em 1em;
        }
        .img-fluid.rounded.shadow {
            border: 2px solid #e9ecef;
            margin-bottom: 1rem;
            background: #fff;
        }
        .btn-warning.disabled-link {
            pointer-events: auto;
            color: #fff;
            background: #ffc107;
            border: none;
            font-weight: 500;
            font-size: 1.05rem;
        }
        .btn-warning.disabled-link:hover {
            background: #ffb300;
            color: #fff;
        }
        .btn-outline-danger {
            min-width: 100px;
            font-weight: 500;
            font-size: 1.05rem;
        }
        .d-flex.gap-2 > * { margin-right: 0.5rem; }
        .d-flex.gap-2 > *:last-child { margin-right: 0; }
    </style>
</head>
<body>
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
                       value="{{ old('url') }}">
                @error('url')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Скачать</button>
        </form>

        @if(isset($thumbnail) && $thumbnail)
            <div class="text-center mb-3">
                <img src="{{ $thumbnail }}" alt="Превью видео" class="img-fluid rounded shadow" style="max-height:220px;object-fit:cover;">
            </div>
        @endif

        @if(isset($formats))
            <div class="mt-4">
                <h3>Доступные форматы:</h3>
                <div class="list-group" id="formats-list">
                    @foreach($formats as $format)
                        <form class="format-download-form d-flex justify-content-between align-items-center mb-2 p-2 rounded shadow-sm" style="background:#f4f6fb;" method="POST" action="{{ route('download.start') }}">
                            @csrf
                            <input type="hidden" name="url" value="{{ request('url') ?? old('url') }}">
                            <input type="hidden" name="format" value="{{ $format['itag'] }}">
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
                                    <form method="POST" action="{{ route('download.cancel', ['id' => $format['active_task_id']]) }}" onsubmit="return confirm('Отменить генерацию этого видео?');">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="/">
                                        <button type="submit" class="btn btn-outline-danger">Отменить</button>
                                    </form>
                                </div>
                            @else
                                <button type="submit" class="btn btn-success">Скачать</button>
                            @endif
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
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
    </script>
</body>
</html> 
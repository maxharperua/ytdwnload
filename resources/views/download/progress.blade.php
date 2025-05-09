@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #282a36 0%, #44475a 100%) !important;
        color: #f8f8f2;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .container {
        max-width: 600px;
        /* margin: 40px auto; */
        width: 100%;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        background: rgba(40, 42, 54, 0.98);
        border-radius: 18px;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.45), 0 1.5px 8px 0 #bd93f9;
        border: 1.5px solid #44475a;
    }
    h3 {
        color: #bd93f9;
        text-shadow: 0 2px 8px #282a36a0;
        margin-bottom: 1.5rem;
    }
    #progress-block {
        margin-bottom: 1.5rem;
    }
    .progress {
        background: #44475a;
        border-radius: 10px;
        box-shadow: 0 2px 8px #6272a440;
        height: 30px;
        overflow: hidden;
    }
    .progress-bar {
        background: linear-gradient(90deg, #bd93f9 0%, #ff79c6 100%);
        color: #f8f8f2;
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 10px 0 0 10px;
        box-shadow: 0 2px 8px #bd93f980;
        transition: width 0.6s cubic-bezier(.4,2,.3,1), background 0.3s;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding-left: 10px;
    }
    #status-text {
        color: #8be9fd;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        margin-top: 0.5rem;
    }
    .btn-success {
        background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
        color: #282a36;
        border: none;
        font-weight: 600;
        font-size: 1.1rem;
        border-radius: 8px;
        min-width: 160px;
        box-shadow: 0 2px 8px #50fa7b80;
        transition: background 0.3s, transform 0.15s, box-shadow 0.3s;
    }
    .btn-success:hover, .btn-success:focus {
        background: linear-gradient(90deg, #8be9fd 0%, #50fa7b 100%);
        color: #282a36;
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 4px 16px #8be9fd80;
    }
    .btn-outline-secondary {
        border: 1.5px solid #bd93f9;
        color: #bd93f9;
        background: transparent;
        font-weight: 600;
        border-radius: 8px;
        transition: background 0.3s, color 0.3s, transform 0.15s, box-shadow 0.3s;
        margin-top: 1.5rem;
    }
    .btn-outline-secondary:hover, .btn-outline-secondary:focus {
        background: #bd93f9;
        color: #282a36;
        box-shadow: 0 4px 16px #bd93f980;
        transform: translateY(-2px) scale(1.04);
    }
    .alert-danger {
        background: #ff5555;
        color: #f8f8f2;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px #ff555580;
    }
    .alert-warning {
        background: linear-gradient(90deg, #f1fa8c 0%, #ffb86c 100%);
        color: #282a36;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px #f1fa8c80;
    }
    /* Прелоадер Dracula */
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
</style>
<body>
    <div class="preloader-overlay" id="preloader" style="display:none;">
        <div class="dracula-spinner"></div>
    </div>
    <div class="container" style="max-width: 600px; margin: 40px auto;">
        <div class="card">
            <div class="card-body text-center">
                <h3>Генерация видео...</h3>
                <div id="progress-block">
                    <div class="progress mb-3" style="height: 30px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="status-text">Ожидание...</div>
                </div>
                <div id="download-block" style="display:none;">
                    <a id="download-link" href="#" class="btn btn-success btn-lg">Скачать видео</a>
                </div>
                <div id="error-block" class="alert alert-danger mt-3" style="display:none;"></div>
                <div id="cancelled-block" style="display:none;">
                    <div class="alert alert-warning">Генерация отменена.</div>
                    <form action="{{ route('youtube.index') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="url" value="{{ $task->url }}">
                        <button type="submit" class="btn btn-outline-secondary">← Назад к выбору видео</button>
                    </form>
                </div>
                <div class="mt-4">
                    <form action="{{ route('youtube.index') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="url" value="{{ $task->url }}">
                        <button type="submit" class="btn btn-outline-secondary">← Назад к выбору видео</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    function checkStatus() {
        fetch("{{ route('download.status', ['id' => $task->id]) }}", {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                let bar = document.getElementById('progress-bar');
                let statusText = document.getElementById('status-text');
                if (data.status === 'error') {
                    document.getElementById('progress-block').style.display = 'none';
                    document.getElementById('error-block').style.display = 'block';
                    document.getElementById('error-block').innerText = data.error || 'Произошла ошибка.';
                } else if (data.status === 'cancelled') {
                    document.getElementById('progress-block').style.display = 'none';
                    document.getElementById('download-block').style.display = 'none';
                    document.getElementById('cancelled-block').style.display = 'block';
                } else if (data.status === 'finished') {
                    bar.style.width = '100%';
                    bar.innerText = '100%';
                    statusText.innerText = 'Готово!';
                    document.getElementById('progress-block').style.display = 'none';
                    document.getElementById('download-block').style.display = 'block';
                    document.getElementById('download-link').href = data.download_url;
                } else {
                    let percent = data.progress || 0;
                    bar.style.width = percent + '%';
                    bar.innerText = percent + '%';
                    statusText.innerText = (data.status === 'processing' ? 'Генерация...' : 'В очереди...');
                    setTimeout(checkStatus, 2000);
                }
            })
            .catch(() => {
                document.getElementById('progress-block').style.display = 'none';
                document.getElementById('error-block').style.display = 'block';
                document.getElementById('error-block').innerText = 'Ошибка соединения.';
            });
    }
    document.addEventListener('DOMContentLoaded', checkStatus);
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
@endsection 
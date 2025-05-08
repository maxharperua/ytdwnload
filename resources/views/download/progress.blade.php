@extends('layouts.app')

@section('content')
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
                <a href="/download" class="btn btn-outline-secondary">← Назад к выбору видео</a>
            </div>
            <div class="mt-4">
                <a href="/download" class="btn btn-outline-secondary">← Назад к выбору видео</a>
            </div>
        </div>
    </div>
</div>
<script>
    function checkStatus() {
        fetch("{{ route('download.status', ['id' => $task->id]) }}")
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
</script>
@endsection 
<template>
    <div class="container" style="max-width: 480px; margin: 40px auto;">
        <div class="card download-card">
            <div class="card-body text-center">
                <h3 class="main-title mb-4 one-line-title">Генерация видео...</h3>
                <div id="progress-block" v-if="!isError && !isCancelled && !isFinished">
                    <div class="custom-progress mb-3">
                        <div class="custom-progress-bar" :style="{ width: progress + '%' }">
                            {{ progress }}%
                        </div>
                    </div>
                    <div id="status-text" class="status-text">
                        {{ statusText }}
                    </div>
                </div>
                <div id="download-block" v-if="isFinished">
                    <a :href="downloadUrl" class="btn custom-download-btn btn-lg">Скачать видео</a>
                </div>
                <div v-if="isError" class="alert custom-alert-danger mt-3">{{ error }}</div>
                <div v-if="isCancelled">
                    <div class="alert custom-alert-warning">Генерация отменена.</div>
                </div>
                <div class="mt-4">
                    <button @click="goBackToFormats" class="btn custom-outline-btn">← Назад к выбору формата</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Download',
    data() {
        return {
            progress: 0,
            statusText: 'Ожидание...',
            isFinished: false,
            isError: false,
            isCancelled: false,
            error: null,
            downloadUrl: null
        }
    },
    methods: {
        async checkStatus() {
            try {
                const response = await fetch(`/api/download/status/${this.$route.params.id}`);
                const data = await response.json();
                if (data.status === 'error') {
                    this.isError = true;
                    this.error = data.error || 'Произошла ошибка.';
                } else if (data.status === 'cancelled') {
                    this.isCancelled = true;
                } else if (data.status === 'finished') {
                    this.progress = 100;
                    this.statusText = 'Готово!';
                    this.isFinished = true;
                    this.downloadUrl = data.download_url;
                } else {
                    this.progress = data.progress || 0;
                    this.statusText = data.status === 'processing' ? 'Генерация...' : 'В очереди...';
                    setTimeout(this.checkStatus, 2000);
                }
            } catch (err) {
                this.isError = true;
                this.error = 'Ошибка соединения.';
            }
        },
        goBackToFormats() {
            this.$router.push({ path: '/', query: { returnFromProgress: '1', taskId: this.$route.params.id } });
        }
    },
    mounted() {
        this.checkStatus();
    }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap');

.download-card {
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.45), 0 1.5px 8px 0 #bd93f9;
    border-radius: 18px;
    border: 1.5px solid #44475a;
    background: rgba(40, 42, 54, 0.98);
}

.main-title {
    text-align: center;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 2rem;
    font-weight: 700;
    color: #bd93f9;
    text-shadow: 0 2px 16px #bd93f9a0, 0 2px 8px #282a36a0;
    margin-bottom: 2rem;
    letter-spacing: 1px;
}
.one-line-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 1.7rem;
    width: 100%;
    margin-bottom: 1.5rem;
}

.custom-progress {
    background: #44475a;
    border-radius: 10px;
    box-shadow: 0 2px 8px #6272a440;
    height: 30px;
    overflow: hidden;
    width: 100%;
}
.custom-progress-bar {
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
    height: 100%;
}
.status-text {
    color: #8be9fd;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    margin-top: 0.5rem;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 500;
}

.custom-download-btn {
    display: block;
    width: auto;
    min-width: 200px;
    max-width: 100%;
    margin: 0 auto 1.5rem auto;
    padding: 0.9rem 2.2rem;
    font-size: 1.18rem;
    text-align: center;
    white-space: nowrap;
    background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
    color: #282a36;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 12px #50fa7b80;
    letter-spacing: 0.5px;
    transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.15s;
    cursor: pointer;
    position: relative;
    z-index: 1;
    outline: none;
}
.custom-download-btn:hover, .custom-download-btn:focus {
    background: linear-gradient(90deg, #8be9fd 0%, #50fa7b 100%);
    color: #282a36;
    box-shadow: 0 4px 24px #8be9fd80, 0 2px 8px #50fa7b80;
    transform: translateY(-2px) scale(1.04);
}
.custom-download-btn:active {
    background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
    color: #282a36;
    box-shadow: 0 2px 8px #50fa7b80;
    transform: scale(0.98);
}

.custom-outline-btn {
    width: 100%;
    border: 1.5px solid #bd93f9;
    color: #bd93f9;
    background: transparent;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 600;
    font-size: 1.1rem;
    border-radius: 8px;
    transition: background 0.3s, color 0.3s, transform 0.15s, box-shadow 0.3s;
    margin-top: 1.5rem;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
}
.custom-outline-btn:hover, .custom-outline-btn:focus {
    background: #bd93f9;
    color: #282a36;
    box-shadow: 0 4px 16px #bd93f980;
    transform: translateY(-2px) scale(1.04);
}

.custom-alert-danger {
    background: #ff5555;
    color: #f8f8f2;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px #ff555580;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 1.08rem;
    font-weight: 500;
}
.custom-alert-warning {
    background: linear-gradient(90deg, #f1fa8c 0%, #ffb86c 100%);
    color: #282a36;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px #f1fa8c80;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 1.08rem;
    font-weight: 500;
}
</style> 
<template>
    <div class="banner-layout">
        <!-- Левый баннер для десктопа -->
        <AdBanner class="side-banner left-banner" :width="300" :height="850" />

        <div class="container" style="max-width: 600px; margin: 40px auto;">
            <div class="card" style="padding: 2.5rem 2rem 2rem 2rem; position: relative;">
                <div v-if="isLoading" class="preloader-overlay">
                    <div class="dracula-spinner"></div>
                </div>
                <h1 class="main-title mb-3">
                    {{ videoData && videoData.title ? videoData.title : 'Video Downloader' }}
                </h1>
                <form v-if="!videoData" @submit.prevent="submitUrl" class="text-center" autocomplete="off">
                    <input
                        type="text"
                        v-model="url"
                        class="form-control custom-input"
                        placeholder="Введите ссылку на YouTube видео"
                        required
                    >
                    <button type="submit" class="btn custom-download-btn mt-3 main-download-btn">Скачать</button>
                </form>
                <!-- Баннер только для мобильных -->
                <AdBanner class="mobile-banner" :width="950" :height="300" />
                <div v-if="!videoData" class="mt-3 p-3 text-center soft-desc small-desc">
                    Сервис ytload.ru предоставляет техническую возможность скачивания видео и музыки исключительно для личного некоммерческого использования. Администрация не несёт ответственности за дальнейшее использование скачанных файлов. Пользователь самостоятельно несёт ответственность за соблюдение авторских прав и законодательства своей страны.
                </div>
                <div v-if="videoData">
                    <div class="preview-block-center">
                        <div class="preview-block">
                            <img :src="videoData.thumbnail" alt="preview" class="img-fluid rounded shadow w-100 preview-img">
                            <button @click="removeVideo" class="remove-video-btn-animated preview-close-btn">
                                <span style="font-size:1.7rem; color:#fff;">×</span>
                            </button>
                        </div>
                    </div>
                    <h4 class="mt-4 mb-3 formats-title">Доступные форматы:</h4>
                    <div class="formats-list mb-4">
                        <div v-for="format in videoData.formats" :key="format.itag" class="format-row-fixed" :class="{'active-format-row': isActiveTask(format)}">
                            <span class="badge format-badge-fixed">{{ format.quality }}</span>
                            <span class="format-type-fixed">{{ format.mimeType }}</span>
                            <template v-if="format.download_url">
                                <a :href="format.download_url" class="btn btn-ready format-download-btn-fixed" target="_blank">Готово</a>
                            </template>
                            <template v-else-if="isActiveTask(format)">
                                <button class="btn btn-warning format-download-btn-fixed" @click="goToProgress(format)">
                                    {{ format.progress !== undefined ? `${format.progress}%` : '0%' }}
                                </button>
                                <button class="btn btn-outline-danger format-cancel-btn-fixed ms-2" @click="cancelTask(format)">Отменить</button>
                            </template>
                            <template v-else-if="format.error">
                                <div class="error-message">{{ format.error }}</div>
                                <button class="btn btn-success format-download-btn-fixed" @click="startDownload(format)">Повторить</button>
                            </template>
                            <template v-else>
                                <button class="btn btn-success format-download-btn-fixed" @click="startDownload(format)">Скачать</button>
                            </template>
                        </div>
                    </div>
                    <div class="mt-3 p-3 text-center soft-desc small-desc">
                        Сервис ytload.ru предоставляет техническую возможность скачивания видео и музыки исключительно для личного некоммерческого использования. Администрация не несёт ответственности за дальнейшее использование скачанных файлов. Пользователь самостоятельно несёт ответственность за соблюдение авторских прав и законодательства своей страны.
                    </div>
                </div>
                <div v-if="error" class="alert alert-danger mt-3">
                    {{ error }}
                </div>
            </div>
        </div>

        <!-- Правый баннер для десктопа -->
        <AdBanner class="side-banner right-banner" :width="300" :height="850" />
    </div>
</template>

<script>
import AdBanner from './AdBanner.vue'

export default {
    name: 'Home',
    components: {
        AdBanner
    },
    data() {
        return {
            url: '',
            error: null,
            isLoading: false,
            videoData: null, // { thumbnail: '', formats: [{quality, mimeType, itag, active_task_id}] }
            activeTaskId: null,
            pollingInterval: null,
            activeTasks: {}, // Хранилище для активных задач
            isPollingRequestActive: false // Флаг для контроля запросов
        }
    },
    mounted() {
        // Восстанавливаем активные задачи из localStorage
        const savedTasks = localStorage.getItem('activeTasks');
        if (savedTasks) {
            try {
                this.activeTasks = JSON.parse(savedTasks);
            } catch (e) {
                console.error('Ошибка при восстановлении активных задач:', e);
            }
        }

        // Если возврат с прогресса — выделяем активный формат
        if (this.$route.query.returnFromProgress && this.$route.query.taskId) {
            this.activeTaskId = this.$route.query.taskId;
        }
        // Если есть сохранённые данные — подставляем их
        if (!this.videoData) {
            const last = localStorage.getItem('lastVideoData');
            if (last) {
                try {
                    const parsed = JSON.parse(last);
                    this.url = parsed.url;
                    this.videoData = parsed.videoData;
                    // Восстанавливаем active_task_id для форматов
                    if (this.videoData && this.videoData.formats) {
                        this.videoData.formats = this.videoData.formats.map(format => {
                            const taskId = this.activeTasks[format.itag];
                            if (taskId) {
                                return { ...format, active_task_id: taskId };
                            }
                            return format;
                        });
                    }
                } catch (e) {
                    console.error('Ошибка при восстановлении данных:', e);
                }
            }
        }
        // Запускаем polling если есть видео
        if (this.videoData) {
            this.startPolling();
        }
    },
    beforeUnmount() {
        this.stopPolling();
    },
    methods: {
        startPolling() {
            this.stopPolling(); // Очищаем предыдущий интервал если есть
            this.pollingInterval = setInterval(async () => {
                if (this.videoData && !this.isPollingRequestActive) {
                    this.isPollingRequestActive = true;
                    await this.updateFormatsStatus();
                    this.isPollingRequestActive = false;
                }
            }, 3000); // Проверяем каждые 3 секунды
        },
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },
        async updateFormatsStatus() {
            try {
                // Проверяем все активные задачи
                const activeFormats = this.videoData.formats.filter(format => {
                    if (!format.active_task_id) return false;
                    console.log('Active format:', format);
                    return true;
                });
                
                if (activeFormats.length === 0) {
                    console.log('No active formats found');
                    return;
                }

                // Получаем статусы для всех активных задач
                const statusPromises = activeFormats.map(async (format) => {
                    if (!format.active_task_id) {
                        console.error('Format without active_task_id:', format);
                        return null;
                    }
                    console.log('Checking status for task:', format.active_task_id);
                    const response = await fetch(`/api/download/status/${format.active_task_id}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                        }
                    });
                    const data = await response.json();
                    console.log('API status response:', data);
                    if (!response.ok) {
                        throw new Error(data.message || 'Ошибка при проверке статуса');
                    }
                    // Возвращаем itag вместе с ответом
                    return { itag: format.itag, status: data };
                });

                const statuses = await Promise.all(statusPromises);
                console.log('Received statuses:', statuses);
                
                // Обновляем статусы в форматах
                this.videoData.formats = this.videoData.formats.map(format => {
                    // Находим статус по itag
                    const found = statuses.find(s => s && s.itag === format.itag);
                    const foundStatus = found ? found.status : null;
                    if (format.active_task_id && foundStatus) {
                        if (foundStatus.status === 'finished' && foundStatus.download_url) {
                            // Добавляем ссылку на скачивание!
                            delete this.activeTasks[format.itag];
                            localStorage.setItem('activeTasks', JSON.stringify(this.activeTasks));
                            return {
                                ...format,
                                active_task_id: null,
                                error: null,
                                progress: 100,
                                download_url: foundStatus.download_url
                            };
                        }
                        if (foundStatus.status === 'completed' || foundStatus.status === 'cancelled' || foundStatus.status === 'error') {
                            delete this.activeTasks[format.itag];
                            localStorage.setItem('activeTasks', JSON.stringify(this.activeTasks));
                            return {
                                ...format,
                                active_task_id: null,
                                error: foundStatus.status === 'error' ? foundStatus.message || 'Произошла ошибка при генерации' : null,
                                progress: undefined
                            };
                        }
                        let progress = foundStatus.progress;
                        if (progress === undefined && foundStatus.percent !== undefined) progress = foundStatus.percent;
                        if (progress === undefined && foundStatus.percentage !== undefined) progress = foundStatus.percentage;
                        if (progress === undefined) progress = 0;
                        return {
                            ...format,
                            progress: Number(progress)
                        };
                    } else {
                        return {
                            ...format,
                            progress: undefined
                        };
                    }
                });

                // Обновляем localStorage
                localStorage.setItem('lastVideoData', JSON.stringify({ url: this.url, videoData: this.videoData }));
            } catch (err) {
                console.error('Ошибка обновления статусов:', err);
            }
        },
        async submitUrl() {
            this.error = null;
            this.isLoading = true;
            try {
                const response = await fetch('/api/youtube/convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    },
                    body: JSON.stringify({ url: this.url })
                });
                const data = await response.json();
                if (response.ok) {
                    this.videoData = data;
                    // Сохраняем в localStorage
                    localStorage.setItem('lastVideoData', JSON.stringify({ url: this.url, videoData: data }));
                    // Запускаем polling после получения данных
                    this.startPolling();
                } else {
                    this.error = data.message || 'Произошла ошибка';
                }
            } catch (err) {
                this.error = 'Ошибка соединения';
            } finally {
                this.isLoading = false;
            }
        },
        removeVideo() {
            this.videoData = null;
            this.url = '';
            localStorage.removeItem('lastVideoData');
            // Очищаем активные задачи
            this.activeTasks = {};
            localStorage.removeItem('activeTasks');
            this.stopPolling();
        },
        async startDownload(format) {
            this.error = null;
            this.isLoading = true;
            try {
                const response = await fetch('/api/download/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    },
                    body: JSON.stringify({ url: this.url, format: format.itag })
                });
                const data = await response.json();
                if (response.ok && data.id) {
                    // Сохраняем активную задачу
                    this.activeTasks[format.itag] = data.id;
                    localStorage.setItem('activeTasks', JSON.stringify(this.activeTasks));
                    
                    // Обновляем формат
                    format.active_task_id = data.id;
                    this.$router.push(`/download/${data.id}`);
                } else if (data.id) {
                    // Сохраняем активную задачу
                    this.activeTasks[format.itag] = data.id;
                    localStorage.setItem('activeTasks', JSON.stringify(this.activeTasks));
                    
                    // Обновляем формат
                    format.active_task_id = data.id;
                    this.$router.push(`/download/${data.id}`);
                } else {
                    this.error = data.message || 'Не удалось запустить задачу.';
                }
            } catch (err) {
                this.error = 'Ошибка соединения.';
            } finally {
                this.isLoading = false;
            }
        },
        isActiveTask(format) {
            return format.active_task_id && format.active_task_id == this.activeTaskId;
        },
        goToProgress(format) {
            this.$router.push(`/download/${format.active_task_id}`);
        },
        async cancelTask(format) {
            if (!confirm('Отменить генерацию этого видео?')) return;
            this.isLoading = true;
            try {
                const response = await fetch(`/api/download/cancel/${format.active_task_id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    // Удаляем задачу из активных
                    delete this.activeTasks[format.itag];
                    localStorage.setItem('activeTasks', JSON.stringify(this.activeTasks));
                    
                    // Обновляем форматы
                    await this.submitUrl();
                    this.activeTaskId = null;
                }
            } catch (err) {
                this.error = 'Ошибка при отмене.';
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap');

.main-title {
    text-align: center;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: #bd93f9;
    text-shadow: 0 2px 16px #bd93f9a0, 0 2px 8px #282a36a0;
    margin-bottom: 2rem;
    letter-spacing: 1px;
}

.preview-block-center {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
    margin-bottom: 1.2rem;
}
.preview-block {
    position: relative;
    width: 90%;
    max-width: 420px;
}
.preview-img {
    max-height: 220px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 24px #282a36a0, 0 1.5px 8px 0 #bd93f9;
}
.preview-close-btn {
    position: absolute;
    top: 8px;
    right: 4px;
    width: 32px;
    height: 32px;
    z-index: 2;
    background: linear-gradient(90deg,#ff5555 0%,#ff79c6 100%);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px #ff79c6b0, 0 0 4px #ff79c6a0;
    transition: box-shadow 0.3s, transform 0.25s cubic-bezier(.4,2,.3,1), background 0.3s;
    outline: none;
    filter: drop-shadow(0 0 4px #ff79c6a0);
    cursor: pointer;
    padding: 0;
}
.preview-close-btn:hover, .preview-close-btn:focus {
    background:linear-gradient(90deg,#ff79c6 0%,#ff5555 100%) !important;
    transform:scale(1.12) rotate(12deg);
    box-shadow:0 4px 16px #ff79c6a0;
}

.formats-title {
    color: #bd93f9;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.2rem;
    margin-top: 1.2rem;
    text-align: left;
}
.formats-list {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}
.format-row-fixed {
    display: flex;
    align-items: center;
    min-height: 48px;
    background: #393b4d;
    border-radius: 10px;
    box-shadow: 0 2px 8px #bd93f930;
    padding: 0 1rem;
    transition: box-shadow 0.2s, background 0.2s;
}
.format-row-fixed:hover {
    background: #44475a;
    box-shadow: 0 4px 16px #bd93f980;
}
.format-badge-fixed {
    background: linear-gradient(90deg, #bd93f9 0%, #ff79c6 100%) !important;
    color: #fff;
    font-size: 1.08rem;
    font-weight: 700;
    border-radius: 6px;
    box-shadow: 0 1px 4px #bd93f980;
    padding: 0.35em 1em;
    margin-right: 1em;
    min-width: 70px;
    text-align: center;
}
.format-type-fixed {
    color: #bcbcbc;
    font-size: 1.05rem;
    font-weight: 500;
    opacity: 0.7;
    margin-right: auto;
    min-width: 48px;
}
.format-download-btn-fixed {
    background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
    color: #282a36;
    border: none;
    font-weight: 600;
    font-size: 1.08rem;
    border-radius: 8px;
    min-width: 120px;
    box-shadow: 0 2px 8px #50fa7b80;
    transition: background 0.3s, transform 0.15s, box-shadow 0.3s;
    padding: 0.55rem 1.2rem;
    margin-left: 0;
    cursor: pointer;
    align-self: center;
}
.format-download-btn-fixed:hover, .format-download-btn-fixed:focus {
    background: linear-gradient(90deg, #8be9fd 0%, #50fa7b 100%);
    color: #282a36;
    transform: translateY(-2px) scale(1.04);
    box-shadow: 0 4px 16px #8be9fd80;
}
.format-download-btn-fixed:active {
    background: linear-gradient(90deg, #50fa7b 0%, #8be9fd 100%);
    color: #282a36;
    transform: scale(0.98);
}

.soft-desc {
    color: #bcbcbc;
    font-size: 1.08rem;
    background: rgba(44,46,60,0.7);
    border-radius: 16px;
    box-shadow: 0 2px 16px #282a3640;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 400;
    opacity: 0.85;
}
.small-desc {
    font-size: 0.92rem;
    padding: 1.1rem 1.2rem;
    margin-top: 1.2rem;
}

.active-format-row {
    border: 2px solid #ffb86c;
    background: #44475a !important;
    box-shadow: 0 4px 16px #ffb86c80;
}
.format-download-btn-fixed.btn-warning {
    background: linear-gradient(90deg, #f1fa8c 0%, #ffb86c 100%) !important;
    color: #282a36 !important;
    font-weight: 700;
    box-shadow: 0 2px 8px #f1fa8c80;
}
.format-cancel-btn-fixed {
    border: 1.5px solid #ff5555;
    color: #ff5555;
    background: transparent;
    font-weight: 600;
    border-radius: 8px;
    transition: background 0.3s, color 0.3s, transform 0.15s, box-shadow 0.3s;
    margin-left: 0.7em;
    padding: 0.55rem 1.2rem;
}
.format-cancel-btn-fixed:hover, .format-cancel-btn-fixed:focus {
    background: #ff5555;
    color: #fff;
    box-shadow: 0 4px 16px #ff555580;
    transform: translateY(-2px) scale(1.04);
}

.error-message {
    color: #ff5555;
    font-size: 0.9rem;
    margin-right: 1rem;
    max-width: 200px;
    text-align: right;
}

.custom-download-btn.main-download-btn {
    margin-top: 1.5rem;
}

.banner-layout {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    position: relative;
}

.side-banner {
    display: none;
    position: sticky;
    top: 40px;
    z-index: 10;
}

.left-banner {
    margin-right: 24px;
}

.right-banner {
    margin-left: 24px;
}

/* Показываем боковые баннеры только на экранах шире 1100px */
@media (min-width: 1100px) {
    .side-banner {
        display: block;
    }
    .mobile-banner {
        display: none !important;
    }
}

/* На мобильных только мобильный баннер */
@media (max-width: 1099px) {
    .side-banner {
        display: none !important;
    }
    .mobile-banner {
        display: block;
    }
}

.btn-ready.format-download-btn-fixed {
    background: linear-gradient(90deg, #6272fa 0%, #8be9fd 100%) !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 2px 8px #6272fa80;
    border: none;
}
</style> 
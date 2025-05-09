<template>
    <div class="banner-layout">
        <!-- Левый баннер для десктопа -->
        <AdBanner class="side-banner left-banner" :width="300" :height="850" />

        <div class="container" style="max-width: 480px; margin: 40px auto;">
            <div class="error-container">
                <div class="error-code">
                    <span class="number">4</span>
                    <div class="ghost">
                        <div class="ghost-body">
                            <div class="ghost-eyes">
                                <div class="eye left"></div>
                                <div class="eye right"></div>
                            </div>
                            <div class="ghost-tail">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                    </div>
                    <span class="number">4</span>
                </div>
                <h2>Страница не найдена</h2>
                <p>Извините, запрашиваемая страница не существует.</p>
                <router-link to="/" class="btn-home">
                    <span>Вернуться на главную</span>
                    <div class="btn-glow"></div>
                </router-link>
            </div>
        </div>

        <!-- Правый баннер для десктопа -->
        <AdBanner class="side-banner right-banner" :width="300" :height="850" />
    </div>
</template>

<script>
import AdBanner from './AdBanner.vue'

export default {
    name: 'NotFound',
    components: { AdBanner }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap');

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

@media (min-width: 1100px) {
    .side-banner {
        display: block;
    }
}

@media (max-width: 1099px) {
    .side-banner {
        display: none !important;
    }
}

.container {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.not-found {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #282a36;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    color: #f8f8f2;
}

.error-container {
    text-align: center;
    padding: 2.5rem 2rem 2rem 2rem;
    background: rgba(40, 42, 54, 0.98);
    border-radius: 18px;
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.45), 0 1.5px 8px 0 #bd93f9;
    border: 1.5px solid #44475a;
    max-width: 480px;
    width: 100%;
    margin: 40px auto;
    position: relative;
}

.error-code {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2rem;
    position: relative;
}

.number {
    font-size: 120px;
    font-weight: bold;
    color: #ff79c6;
    text-shadow: 0 0 10px rgba(255, 121, 198, 0.5);
    animation: pulse 2s infinite;
    font-family: inherit;
}

.ghost {
    width: 100px;
    height: 120px;
    margin: 0 20px;
    animation: float 3s ease-in-out infinite;
}

.ghost-body {
    background: #bd93f9;
    width: 100%;
    height: 100%;
    border-radius: 50px 50px 0 0;
    position: relative;
    box-shadow: 0 0 20px rgba(189, 147, 249, 0.5);
}

.ghost-eyes {
    display: flex;
    justify-content: space-around;
    padding: 40px 20px;
}

.eye {
    width: 12px;
    height: 12px;
    background: #282a36;
    border-radius: 50%;
    animation: blink 3s infinite;
}

.ghost-tail {
    display: flex;
    position: absolute;
    bottom: -10px;
    width: 100%;
}

.ghost-tail div {
    flex: 1;
    height: 20px;
    background: #bd93f9;
    border-radius: 0 0 50% 50%;
}

h2 {
    font-size: 2rem;
    margin: 20px 0 10px 0;
    color: #50fa7b;
    text-shadow: 0 2px 16px #50fa7b60, 0 2px 8px #282a36a0;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 700;
}

p {
    color: #bcbcbc;
    margin-bottom: 30px;
    font-size: 1.08rem;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-weight: 400;
}

.btn-home {
    display: inline-block;
    position: relative;
    padding: 12px 24px;
    background: #bd93f9;
    color: #282a36;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    overflow: hidden;
    transition: all 0.3s ease;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
    font-size: 1.08rem;
    letter-spacing: 0.5px;
}

.btn-home:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(189, 147, 249, 0.4);
}

.btn-glow {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    animation: glow 2s infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.8;
    }
}

@keyframes blink {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(0.1);
    }
}

@keyframes glow {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

@media (max-width: 768px) {
    .error-container {
        max-width: 98vw;
        padding: 1.2rem 0.5rem 1.5rem 0.5rem;
    }
    .number {
        font-size: 80px;
    }
    .ghost {
        width: 80px;
        height: 100px;
    }
    h2 {
        font-size: 1.3rem;
    }
}
</style> 
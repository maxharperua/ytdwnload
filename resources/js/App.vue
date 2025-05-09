<template>
    <div>
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: 'App'
}
</script>

<style>
body {
    background: linear-gradient(135deg, #282a36 0%, #44475a 100%) !important;
    color: #f8f8f2;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

.container {
    max-width: 600px;
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
    padding: 2rem;
    margin: 1rem;
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
    padding: 0.75rem 1.5rem;
    cursor: pointer;
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
    padding: 0.75rem 1.5rem;
    cursor: pointer;
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
    padding: 1rem;
    margin: 1rem 0;
}

.alert-warning {
    background: linear-gradient(90deg, #f1fa8c 0%, #ffb86c 100%);
    color: #282a36;
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 8px #f1fa8c80;
    padding: 1rem;
    margin: 1rem 0;
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

input.form-control {
    background: #44475a;
    border: 1.5px solid #6272a4;
    border-radius: 8px;
    color: #f8f8f2;
    padding: 0.75rem 1rem;
    width: 100%;
    font-size: 1rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

input.form-control:focus {
    outline: none;
    border-color: #bd93f9;
    box-shadow: 0 0 0 2px #bd93f940;
}

input.form-control::placeholder {
    color: #6272a4;
}
</style> 
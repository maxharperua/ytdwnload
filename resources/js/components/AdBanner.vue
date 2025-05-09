<template>
    <div class="ad-banner-container" :style="containerStyle" ref="bannerContainer"></div>
</template>

<script>
export default {
    name: 'AdBanner',
    props: {
        width: {
            type: [String, Number],
            default: 950
        },
        height: {
            type: [String, Number],
            default: 300
        },
        adSlot: {
            type: [String, Number],
            default: '1829001'
        }
    },
    computed: {
        containerStyle() {
            return {
                display: 'flex',
                justifyContent: 'center',
                margin: '20px 0',
                minWidth: this.width + 'px',
                minHeight: this.height + 'px',
            };
        }
    },
    mounted() {
        // Очищаем контейнер
        this.$refs.bannerContainer.innerHTML = '';
        // Создаём <ins>
        const ins = document.createElement('ins');
        ins.className = 'mrg-tag';
        ins.style.display = 'inline-block';
        ins.style.width = this.width + 'px';
        ins.style.height = this.height + 'px';
        ins.setAttribute('data-ad-client', 'ad-1829001');
        ins.setAttribute('data-ad-slot', this.adSlot);
        this.$refs.bannerContainer.appendChild(ins);
        // Загружаем скрипт если не был загружен
        if (!window._mailruAdAsyncLoaded) {
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://ad.mail.ru/static/ads-async.js';
            script.onload = () => {
                window._mailruAdAsyncLoaded = true;
                if (window.MRGtag) window.MRGtag.push({});
            };
            document.body.appendChild(script);
        } else {
            if (window.MRGtag) window.MRGtag.push({});
        }
    }
}
</script>

<style scoped>
.ad-banner-container {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}
</style> 
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/svg+xml" href="/logo.svg">
    <title>YouTube Downloader | Скачать видео с YouTube, ВК, RuTube бесплатно</title>
    <meta name="description" content="Скачивайте видео в HD качестве, музыку MP3 и фото с YouTube, ВКонтакте, RuTube, Shorts бесплатно и без регистрации. Поддерживает все форматы: MP4, MP3, WEBM. Быстрый онлайн загрузчик — просто вставьте ссылку!">
    <meta name="keywords" content="скачать видео, скачать музыку, скачать фото, youtube downloader, вконтакте, rutube, shorts, загрузчик, онлайн, бесплатно, без регистрации, mp4, mp3, hd, 4k">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#FF0000">
    <link rel="canonical" href="https://ytload.ru/">
    <link rel="alternate" href="https://ytload.ru/" hreflang="ru">
    <link rel="alternate" href="https://ytload.ru/" hreflang="x-default">
    
    <!-- Open Graph и Twitter -->
    <meta property="og:title" content="YouTube Downloader | Скачать видео с YouTube, ВК, RuTube бесплатно">
    <meta property="og:description" content="Скачивайте видео в HD качестве, музыку MP3 и фото с YouTube, ВКонтакте, RuTube, Shorts бесплатно и без регистрации. Поддерживает все форматы: MP4, MP3, WEBM.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ytload.ru/">
    <meta property="og:image" content="/preview.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="YTLoad.ru">
    <meta property="og:locale" content="ru_RU">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="YouTube Downloader | Скачать видео с YouTube, ВК, RuTube бесплатно">
    <meta name="twitter:description" content="Скачивайте видео в HD качестве, музыку MP3 и фото с YouTube, ВКонтакте, RuTube, Shorts бесплатно и без регистрации. Поддерживает все форматы: MP4, MP3, WEBM.">
    <meta name="twitter:image" content="/preview.png">
    <script async src="https://privacy-cs.mail.ru/static/sync-loader.js"></script>
    <script>
    function getSyncId() {
        try {
            return JSON.parse(localStorage.getItem('rb_sync_id')).fpid || "";
        } catch (e) {
            return "";
        }
    }
    </script>
</head>
<body>
    <div id="app"></div>
    <noscript><div><img src="https://mc.yandex.ru/watch/101710991" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
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
</body>
</html> 
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class VideoUrl implements Rule
{
    /**
     * Список поддерживаемых доменов
     */
    protected $supportedDomains = [
        'youtube.com',
        'www.youtube.com',
        'youtu.be',
        'vk.com',
        'vkvideo.ru',
        'www.vk.com',
        'vk.ru',
        'www.vk.ru',
        'tiktok.com',
        'www.tiktok.com',
        'vm.tiktok.com',
        'vt.tiktok.com',
        'tiktok.ru',
        'rutube.ru',
        'dzen.ru',
        'dzen.ru/video',
        'ok.ru',
        'ok.ru/video',
        'mail.ru',
        'mail.ru/video',
        'my.mail.ru',
        'my.mail.ru/video',
        'pinterest.com',
        'pinterest.ru',
        'pinterest.com/pin',
        'pinterest.ru/pin',
        'facebook.com',
        'fb.com',
        'facebook.com/watch',
        'fb.com/watch',
        'instagram.com',
        'instagram.com/reel',
        'instagram.com/p',
        'twitter.com',
        'x.com',
        'twitter.com/i/videos',
        'x.com/i/videos',
        'twitch.tv',
        'twitch.tv/videos',
        'twitch.tv/clip',
        'bilibili.com',
        'bilibili.com/video',
        'b23.tv',
        'b23.tv/video',
        'vimeo.com',
        'vimeo.com/video',
        'dailymotion.com',
        'dailymotion.com/video',
        'reddit.com',
        'reddit.com/r',
        'reddit.com/video',
        'reddit.com/r/videos',
        'reddit.com/r/videoreddit',
        'reddit.com/r/videoreddits',
        'reddit.com/r/videoredditvideos',
        'reddit.com/r/videoredditvideo'
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        // Проверяем, соответствует ли домен одному из поддерживаемых
        foreach ($this->supportedDomains as $domain) {
            if (str_ends_with($host, $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Пожалуйста, введите ссылку на видео с YouTube, VK или TikTok.';
    }
} 
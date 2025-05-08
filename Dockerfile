FROM php:8.2-fpm-bullseye

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    python3 \
    python3-pip \
    ffmpeg

# Установка yt-dlp
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Очистка кэша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Получение последней версии Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка рабочей директории
WORKDIR /var/www

# Копирование файлов проекта
COPY . /var/www

# Установка прав доступа
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/database

# Открытие порта 9000
EXPOSE 9000

CMD ["php-fpm"]
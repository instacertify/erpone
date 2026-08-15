FROM php:8.5-fpm-bookworm

LABEL org.opencontainers.image.title="Instacertify ERP PHP"
LABEL org.opencontainers.image.description="PHP-FPM 8.5 runtime for Instacertify ERP"

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl unzip zip libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libicu-dev libonig-dev libxml2-dev chromium \
        fonts-liberation ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql pgsql bcmath intl gd zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium
ENV CHROME_PATH=/usr/bin/chromium

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-erp.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-erp.conf

RUN groupmod -o -g ${WWWGROUP} www-data \
    && usermod -o -u ${WWWUSER} -g www-data www-data \
    && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Keep root for one-click install (composer/migrate). php-fpm workers use www-data via pool config.
EXPOSE 9000
CMD ["php-fpm"]

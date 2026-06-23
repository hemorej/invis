FROM php:8.5-apache

# Install system dependencies and PHP extensions required by Kirby
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libavif-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        libzip-dev \
        git \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-avif \
    && docker-php-ext-install -j"$(nproc)" \
        curl \
        dom \
        gd \
        iconv \
        mbstring \
        simplexml \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable required Apache modules
RUN a2enmod rewrite deflate expires headers remoteip

# Apache site config (AllowOverride All for .htaccess)
COPY docker/apache-site.conf /etc/apache2/sites-available/000-default.conf

# PHP config
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

# Copy application source — submodules must be initialized locally before building:
#   git submodule update --init --recursive
# Submodule commits are pinned in .gitmodules and preserved by the COPY.
COPY . .

# Set ownership and permissions
# content/ will be volume-mounted; set writable dirs for runtime
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 \
        content \
        media \
        site/sessions \
        site/cache \
        site/accounts

EXPOSE 80

# Docker supports --env-file at runtime:
#   docker run --env-file .env.production ...
# or via docker-compose env_file directive.

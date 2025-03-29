FROM composer:latest AS base

FROM php:cli

COPY --from=base /usr/bin/composer /usr/bin/composer

# Install PHP extensions for database and CSV handling
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libpq-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install \
    pdo_pgsql \
    zip

WORKDIR /app

# Keep container running with shell
CMD ["/bin/sh"]

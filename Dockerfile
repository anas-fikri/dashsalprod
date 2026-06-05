FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    icu-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    mbstring \
    zip \
    gd \
    xml \
    opcache \
    pcntl \
    intl

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && yes "" | pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]

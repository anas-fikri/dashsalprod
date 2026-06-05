# Stack dan Docker MVP - Dashboard Penggajian Industri

Dokumen ini menjelaskan konfigurasi stack runtime aplikasi dan cara menjalankan aplikasi menggunakan Docker secara lokal.

## 1. Persyaratan Sistem Lokal (Tanpa Docker)
* **PHP**: ^8.3 (dengan ekstensi `pdo_sqlite`, `mbstring`, `xml`, `gd`, `zip`)
* **Node.js**: ^20.x & **NPM**
* **Composer**: ^2.x
* **SQLite**

---

## 2. Struktur Dockerfile (`Dockerfile`)
Untuk portabilitas, aplikasi dapat menggunakan `Dockerfile` multi-stage untuk memisahkan build frontend dan production runtime:

```dockerfile
# Stage 1: Build Frontend Assets
FROM node:20-alpine AS assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Runtime Production
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Install package sistem & PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev \
    && docker-php-ext-install pdo_sqlite gd zip

# Copy composer files & project
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Autoload dump
RUN composer dump-autoload --no-dev --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/var/www/html/docker/supervisord.conf"]
```

---

## 3. Docker Compose Configuration (`docker-compose.yml`)
Untuk menjalankan server web dan database SQLite di container secara mudah:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:80"
    volumes:
      - ./database:/var/www/html/database
      - ./storage:/var/www/html/storage
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - APP_KEY=base64:aDtRTnPm4csZhCzyW1JkaQBjCWIGiz2BCTBepuy4qy0=
      - DB_CONNECTION=sqlite
      - DB_DATABASE=/var/www/html/database/database.sqlite
```

---

## 4. Langkah Menjalankan Docker
1. Pastikan folder `database/` memiliki file database SQLite kosong:
   ```bash
   touch database/database.sqlite
   ```
2. Build dan jalankan container:
   ```bash
   docker compose up --build -d
   ```
3. Jalankan migrasi di dalam container:
   ```bash
   docker compose exec app php artisan migrate --force
   ```


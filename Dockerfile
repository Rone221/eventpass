# ─────────────────────────────────────────────────────────────
#  EventPass — image de déploiement (Railway, Render, etc.)
#  Étape 1 : build des assets front (Vite + Tailwind) avec Node
#  Étape 2 : runtime PHP 8.2 + Laravel
# ─────────────────────────────────────────────────────────────

# ---- Étape 1 : assets ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm ci && npm run build

# ---- Étape 2 : application PHP ----
FROM php:8.2-cli-alpine AS app

# Extensions PHP requises (sqlite, gd pour les QR codes, etc.)
RUN apk add --no-cache \
        git unzip libzip-dev oniguruma-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev icu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_sqlite mbstring gd zip bcmath \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dépendances PHP (couche cache : on copie d'abord composer.*)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Code applicatif + assets compilés
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Script de démarrage
RUN chmod +x docker/start.sh

EXPOSE 8080
CMD ["sh", "docker/start.sh"]

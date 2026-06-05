#!/bin/sh
# ─────────────────────────────────────────────────────────────
#  Démarrage de l'application en production.
#  Recrée l'arborescence storage (volume monté vide), prépare la
#  base SQLite persistante, applique les migrations, puis sert l'app.
# ─────────────────────────────────────────────────────────────
set -e
cd /app

# 1. Arborescence storage (le volume Railway est monté vide sur /app/storage)
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/app/public \
         storage/logs

# 2. Base SQLite sur le volume persistant
DB_FILE="${DB_DATABASE:-/app/storage/database.sqlite}"
[ -f "$DB_FILE" ] || touch "$DB_FILE"

# 3. Migrations (+ seed au tout premier déploiement si la base est vide)
php artisan migrate --force
php artisan db:seed --force || true

# 4. Lien public/storage -> storage/app/public (pour les images uploadées)
php artisan storage:link || true

# 5. Caches de prod (on saute route:cache : une route utilise une closure)
php artisan config:cache
php artisan view:cache

# 6. Serveur (Railway fournit $PORT)
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"

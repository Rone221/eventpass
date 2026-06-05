<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Petit utilitaire de résolution d'URL pour les médias.
 *
 * - Si le chemin est déjà une URL absolue (ex. photo Unsplash de démo),
 *   on la renvoie telle quelle.
 * - Sinon, on considère un chemin relatif sur le disque "public"
 *   (storage/app/public) exposé via /storage (php artisan storage:link).
 */
class Media
{
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

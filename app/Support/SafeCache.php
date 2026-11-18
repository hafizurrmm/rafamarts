<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * A self-healing wrapper around Cache::remember() for values that hold real PHP
 * objects (Eloquent Collections/Models), not just plain scalars/arrays.
 *
 * Why this exists: the file cache driver is not atomic on every OS — on Windows in
 * particular (Laragon/XAMPP/php artisan serve), one request can be reading a cache
 * file at the exact moment another request (e.g. an admin saving a product image,
 * which fires Cache::forget() + a fresh write) is replacing it. The reader then
 * unserializes a half-written file and gets a PHP "incomplete object" fatal error
 * instead of the real Collection — which is exactly the
 * "script tried to call a method on an incomplete object ... Eloquent\Collection"
 * error. Clearing the cache "fixes" it only until the next unlucky race.
 *
 * This helper detects that corrupted result (instead of crashing the page),
 * immediately discards the bad cache entry, and recomputes + re-caches a fresh
 * value — so a corrupted read self-heals on the very next request instead of
 * needing a manual `php artisan cache:clear`.
 */
class SafeCache
{
    public static function remember(string $key, \DateTimeInterface|\DateInterval|int $ttl, \Closure $callback): mixed
    {
        try {
            $value = Cache::remember($key, $ttl, $callback);

            if (self::isCorrupted($value)) {
                throw new \RuntimeException("Corrupted cache entry for [{$key}]");
            }

            return $value;
        } catch (\Throwable $e) {
            // Whatever went wrong (corrupted unserialize, incomplete object, etc.) —
            // drop the bad entry and serve a correct, freshly-computed value right now.
            Cache::forget($key);

            $fresh = $callback();

            try {
                Cache::put($key, $fresh, $ttl);
            } catch (\Throwable $ignored) {
                // If even writing the cache fails, that's fine — the page still
                // renders correctly with $fresh, just without caching this time.
            }

            return $fresh;
        }
    }

    private static function isCorrupted(mixed $value): bool
    {
        if (is_object($value) && $value::class === '__PHP_Incomplete_class') {
            return true;
        }

        // A Collection whose items are themselves incomplete objects is just as broken.
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->contains(fn ($item) => is_object($item) && $item::class === '__PHP_Incomplete_class');
        }

        return false;
    }
}

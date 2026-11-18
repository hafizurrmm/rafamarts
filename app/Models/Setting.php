<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    // For settings that store an array (e.g. multiple banner images from a Filament FileUpload)
    public static function getArray(string $key, array $default = []): array
    {
        $raw = static::get($key);

        if (blank($raw)) {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    // Save many key => value pairs at once (used by settings forms)
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }
}

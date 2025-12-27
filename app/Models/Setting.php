<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'settings_';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . 'get_' . $key;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, ?string $label = null, ?string $type = 'text', ?string $group = 'system'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'label' => $label ?? ucfirst(str_replace('_', ' ', $key)),
                'type' => $type,
                'group' => $group,
            ]
        );
        
        // Clear cache when setting is updated
        self::clearCache();
    }

    /**
     * Get all settings grouped by group
     */
    public static function getGrouped(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'grouped';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return static::orderBy('group')
                ->orderBy('id')
                ->get()
                ->groupBy('group')
                ->toArray();
        });
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAll(): array
    {
        $cacheKey = self::CACHE_PREFIX . 'all';
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        // Clear all cache keys that start with settings prefix
        Cache::forget(self::CACHE_PREFIX . 'all');
        Cache::forget(self::CACHE_PREFIX . 'grouped');
        
        // Clear individual setting caches
        // Note: We can't clear all individual caches without knowing all keys,
        // but they will expire naturally after TTL
        // If needed, we can store keys in a separate cache entry
        $settings = static::pluck('key')->toArray();
        foreach ($settings as $key) {
            Cache::forget(self::CACHE_PREFIX . 'get_' . $key);
        }
    }

    /**
     * Boot method to clear cache on model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are created, updated, or deleted
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}

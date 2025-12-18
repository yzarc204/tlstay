<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings grouped by group
     */
    public static function getGrouped(): array
    {
        return static::orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group')
            ->toArray();
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAll(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}

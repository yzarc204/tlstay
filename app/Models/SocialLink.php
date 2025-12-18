<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'url',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get active social links ordered by order
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }
}

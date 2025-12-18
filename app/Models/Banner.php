<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'link',
        'order',
        'is_active',
        'start_date',
        'end_date',
        'show_text',
        'text_title',
        'text_subtitle',
        'text_line1',
        'text_line2',
        'text_line3',
        'text_position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_text' => 'boolean',
        'order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get active sliders
     */
    public static function getActive(int $limit = null)
    {
        $query = static::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->orderBy('order')
            ->orderBy('id');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}

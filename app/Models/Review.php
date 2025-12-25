<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'house_id',
        'rating',
        'comment',
        'images',
        'manager_response',
        'manager_response_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'images' => 'array',
            'manager_response_at' => 'datetime',
        ];
    }

    /**
     * Get the booking that this review belongs to.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the house that was reviewed.
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}

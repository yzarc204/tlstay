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
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Khi tạo review mới, cập nhật rating của nhà
        static::created(function ($review) {
            static::updateHouseRating($review->house_id);
        });

        // Khi xóa review, cập nhật lại rating của nhà
        static::deleted(function ($review) {
            static::updateHouseRating($review->house_id);
        });

        // Khi cập nhật review (ví dụ: thay đổi rating), cập nhật lại rating của nhà
        static::updated(function ($review) {
            // Chỉ cập nhật nếu rating hoặc house_id thay đổi
            if ($review->isDirty('rating') || $review->isDirty('house_id')) {
                // Cập nhật nhà mới nếu house_id thay đổi
                if ($review->isDirty('house_id')) {
                    static::updateHouseRating($review->getOriginal('house_id'));
                }
                static::updateHouseRating($review->house_id);
            }
        });
    }

    /**
     * Cập nhật rating và số lượng đánh giá cho nhà trọ.
     *
     * @param int $houseId
     * @return void
     */
    protected static function updateHouseRating($houseId)
    {
        if (!$houseId) {
            return;
        }

        $house = House::find($houseId);
        if (!$house) {
            return;
        }

        $reviews = static::where('house_id', $houseId)->get();

        if ($reviews->count() > 0) {
            $averageRating = round($reviews->avg('rating'), 2);
            $house->update([
                'rating' => $averageRating,
                'reviews' => $reviews->count(),
            ]);
        } else {
            // Nếu không còn đánh giá nào, đặt về 0
            $house->update([
                'rating' => 0,
                'reviews' => 0,
            ]);
        }
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

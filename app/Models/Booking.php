<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'house_id',
        'room_id',
        'start_date',
        'end_date',
        'status',
        'booking_status',
        'total_price',
        'discount_amount',
        'tenant_name',
        'notes',
        'payment_status',
        'payment_method',
        'vnpay_transaction_id',
        'paid_at',
        'user_signature',
        'signed_at',
        'contract_signed',
        'booking_code',
        // Price breakdown fields
        'full_months',
        'full_weeks',
        'remaining_days',
        'month_unit_price',
        'week_unit_price',
        'day_unit_price',
        'months_price',
        'weeks_price',
        'remaining_price',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'signed_at' => 'datetime',
            'contract_signed' => 'boolean',
            // Price breakdown casts
            'month_unit_price' => 'decimal:2',
            'week_unit_price' => 'decimal:2',
            'day_unit_price' => 'decimal:2',
            'months_price' => 'decimal:2',
            'weeks_price' => 'decimal:2',
            'remaining_price' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the house for the booking.
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Get the room for the booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the invoices for the booking.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the review for the booking.
     */
    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }

}

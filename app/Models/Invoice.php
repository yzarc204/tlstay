<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'month',
        'year',
        'start_date',
        'end_date',
        'amount',
        'electricity_amount',
        'water_amount',
        'other_fees',
        'status',
        'due_date',
        'paid_at',
        'notes',
        'invoice_code',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'electricity_amount' => 'decimal:2',
            'water_amount' => 'decimal:2',
            'other_fees' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the booking that owns the invoice.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get month/year string for display (backward compatibility).
     */
    public function getMonthYearAttribute(): string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y');
        }
        if ($this->month && $this->year) {
            return "{$this->month}/{$this->year}";
        }
        return '';
    }
}

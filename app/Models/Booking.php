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
     * Generate booking code based on rules:
     * [Tên đường viết tắt][ID user][ngày][tháng][năm][số thứ tự]
     * 
     * @param House $house
     * @param int $userId
     * @return string
     */
    public static function generateBookingCode(House $house, int $userId): string
    {
        // 1. Get street name abbreviation
        $streetAbbr = '';
        if ($house->street_id) {
            $street = \App\Models\Address::find($house->street_id);
            if ($street && $street->name) {
                // Remove Vietnamese accents and get first letter of each word
                $streetName = self::removeVietnameseAccents($street->name);
                $words = explode(' ', trim($streetName));
                foreach ($words as $word) {
                    if (!empty($word)) {
                        $streetAbbr .= strtoupper(substr($word, 0, 1));
                    }
                }
            }
        }
        
        // Fallback: if no street, use first letters of house name
        if (empty($streetAbbr)) {
            $houseName = self::removeVietnameseAccents($house->name ?? '');
            $words = explode(' ', trim($houseName));
            foreach ($words as $word) {
                if (!empty($word)) {
                    $streetAbbr .= strtoupper(substr($word, 0, 1));
                }
            }
        }
        
        // Final fallback: if still empty, use "HD" (Hợp đồng)
        if (empty($streetAbbr)) {
            $streetAbbr = 'HD';
        }
        
        // 2. Get user ID
        $userCode = (string) $userId;
        
        // 3. Get date (ddMMyyyy)
        $now = now();
        $dateCode = $now->format('d') . $now->format('m') . $now->format('Y');
        
        // 4. Get sequence number for today (001, 002, ...)
        // Count bookings created today (before creating this one)
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        
        $todayBookingsCount = self::whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();
        
        $sequenceNumber = str_pad((string) ($todayBookingsCount + 1), 3, '0', STR_PAD_LEFT);
        
        // Combine all parts
        $bookingCode = $streetAbbr . $userCode . $dateCode . $sequenceNumber;
        
        // Ensure uniqueness - if code exists, increment sequence number
        $attempts = 0;
        while (self::where('booking_code', $bookingCode)->exists() && $attempts < 100) {
            $todayBookingsCount++;
            $sequenceNumber = str_pad((string) ($todayBookingsCount + 1), 3, '0', STR_PAD_LEFT);
            $bookingCode = $streetAbbr . $userCode . $dateCode . $sequenceNumber;
            $attempts++;
        }
        
        return $bookingCode;
    }

    /**
     * Remove Vietnamese accents from string
     * 
     * @param string $str
     * @return string
     */
    private static function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ'
        ];
        
        $noAccents = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];
        
        return str_replace($accents, $noAccents, $str);
    }
}

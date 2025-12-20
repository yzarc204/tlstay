<?php

namespace App\Helpers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Str;

class CodeGenerator
{
    /**
     * Generate a unique booking code
     * Format: BK-{random 8 characters uppercase alphanumeric}
     * Example: BK-A1B2C3D4
     *
     * @return string
     */
    public static function generateBookingCode(): string
    {
        do {
            $code = 'BK-' . strtoupper(Str::random(8));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique invoice code
     * Format: INV-{random 8 characters uppercase alphanumeric}
     * Example: INV-X9Y8Z7W6
     *
     * @return string
     */
    public static function generateInvoiceCode(): string
    {
        do {
            $code = 'INV-' . strtoupper(Str::random(8));
        } while (Invoice::where('invoice_code', $code)->exists());

        return $code;
    }
}

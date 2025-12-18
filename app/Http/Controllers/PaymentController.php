<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Wallet;
use App\Models\CompanyInformation;
use App\Helpers\VietQRHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Show payment page for booking
     */
    public function create(Request $request, $bookingId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $booking = Booking::with(['house', 'room'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if booking is already paid
        if ($booking->payment_status === 'paid') {
            return redirect()->route('booking.success', $booking->id)
                ->with('info', 'Đơn đặt phòng này đã được thanh toán.');
        }

        // Get company payment information
        $company = CompanyInformation::getInstance();
        
        if (!$company->bank_name || !$company->bank_account_number || !$company->bank_account_holder) {
            return back()->with('error', 'Thông tin thanh toán của doanh nghiệp chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        }

        // Generate VietQR URL
        $qrUrl = VietQRHelper::generateQRUrl(
            $company->bank_name,
            $company->bank_account_number,
            (int) $booking->total_price,
            $booking->booking_code,
            $company->bank_account_holder
        );

        return Inertia::render('Payment/Create', [
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'house_name' => $booking->house->name,
                'room_number' => $booking->room->room_number,
                'total_price' => (float) $booking->total_price,
                'start_date' => $booking->start_date->format('Y-m-d'),
                'end_date' => $booking->end_date->format('Y-m-d'),
            ],
            'payment_info' => [
                'bank_name' => $company->bank_name,
                'bank_account_number' => $company->bank_account_number,
                'bank_account_holder' => $company->bank_account_holder,
            ],
            'qr_url' => $qrUrl,
        ]);
    }

    /**
     * Confirm payment (sandbox - no actual verification)
     */
    public function confirm(Request $request, $bookingId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $booking = Booking::with(['house', 'room'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if booking is already paid
        if ($booking->payment_status === 'paid') {
            return redirect()->route('booking.success', $booking->id)
                ->with('info', 'Đơn đặt phòng này đã được thanh toán.');
        }

        // Update booking payment status (sandbox - no verification)
        $booking->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'bank_transfer',
        ]);

        // Update room status to occupied
        $room = $booking->room;
        $room->update([
            'status' => 'occupied',
            'current_tenant_id' => $user->id,
            'rental_start_date' => $booking->start_date,
            'rental_end_date' => $booking->end_date,
        ]);

        Log::info('Booking payment confirmed (sandbox)', [
            'booking_id' => $bookingId,
            'user_id' => $user->id,
            'amount' => $booking->total_price,
        ]);

        return redirect()->route('booking.success', $booking->id)
            ->with('success', 'Thanh toán thành công! Đơn đặt phòng của bạn đã được xác nhận.');
    }

    /**
     * Pay invoice using wallet
     */
    public function payInvoiceWithWallet(Request $request, $invoiceId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return back()->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        $invoice = \App\Models\Invoice::where('id', $invoiceId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return back()->with('info', 'Hóa đơn này đã được thanh toán.');
        }

        // Calculate total amount
        $totalAmount = (float) (
            $invoice->amount +
            $invoice->electricity_amount +
            $invoice->water_amount +
            ($invoice->other_fees ?? 0)
        );

        // Get or create wallet
        $wallet = Wallet::getOrCreateForUser($user->id);

        // Check if wallet has enough balance
        if ($wallet->balance < $totalAmount) {
            return back()->with('error', 'Số dư ví không đủ để thanh toán. Số dư hiện tại: ' . number_format($wallet->balance, 0, ',', '.') . ' ₫');
        }

        try {
            // Deduct from wallet
            $wallet->debit(
                $totalAmount,
                "Thanh toán hóa đơn tháng {$invoice->month}/{$invoice->year}",
                'Invoice',
                $invoice->id
            );

            // Update invoice status
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            Log::info('Invoice paid with wallet', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'wallet_balance_after' => $wallet->balance,
            ]);

            return back()->with('success', 'Thanh toán hóa đơn thành công bằng ví!');
        } catch (\Exception $e) {
            Log::error('Error paying invoice with wallet', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi thanh toán: ' . $e->getMessage());
        }
    }
}

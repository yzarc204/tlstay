<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CompanyInformation;
use App\Services\PaymentService;
use App\Helpers\VietQRHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Show payment page for booking
     * Flow: Chọn phòng → Đặt phòng → Thanh toán (trang này) → Trang thành công
     */
    public function create(Request $request, $bookingId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        try {
            // Find booking with relationships
            $booking = Booking::with(['house', 'room'])->find($bookingId);
            
            if (!$booking) {
                \Log::warning('Payment: Booking not found', ['booking_id' => $bookingId, 'user_id' => $user->id]);
                return redirect()->route('history.index')
                    ->with('error', 'Đơn đặt phòng không tồn tại.');
            }
            
            // Check if user owns this booking
            if ($booking->user_id != $user->id) {
                \Log::warning('Payment: User does not own booking', [
                    'booking_id' => $bookingId,
                    'booking_user_id' => $booking->user_id,
                    'current_user_id' => $user->id
                ]);
                return redirect()->route('history.index')
                    ->with('error', 'Bạn không có quyền truy cập đơn đặt phòng này.');
            }

            // If already paid, redirect to next step
            if ($booking->payment_status === 'paid') {
                // If contract is already signed, redirect to success page
                if ($booking->contract_signed) {
                    return redirect()->route('booking.success', $booking->id)
                        ->with('info', 'Đơn đặt phòng này đã được thanh toán và ký hợp đồng.');
                }
                // Otherwise, redirect to contract signing page
                return redirect()->route('contract.sign.show', ['booking' => $booking->id])
                    ->with('info', 'Đơn đặt phòng này đã được thanh toán. Vui lòng ký hợp đồng.');
            }

            // Validate relationships exist
            if (!$booking->house) {
                \Log::error('Payment: House not found for booking', ['booking_id' => $bookingId]);
                return redirect()->route('history.index')
                    ->with('error', 'Thông tin nhà trọ không tồn tại.');
            }
            
            if (!$booking->room) {
                \Log::error('Payment: Room not found for booking', ['booking_id' => $bookingId]);
                return redirect()->route('history.index')
                    ->with('error', 'Thông tin phòng không tồn tại.');
            }

            // Get company payment information
            $company = CompanyInformation::getInstance();
            
            if (!$company->bank_name || !$company->bank_account_number || !$company->bank_account_holder) {
                \Log::warning('Payment: Company payment info not configured');
                return redirect()->route('history.index')
                    ->with('error', 'Thông tin thanh toán của doanh nghiệp chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
            }

            // Generate VietQR URL
            $bookingCode = $booking->booking_code ?? (string) $booking->id;
            $qrUrl = VietQRHelper::generateQRUrl(
                $company->bank_name,
                $company->bank_account_number,
                (int) $booking->total_price,
                $bookingCode,
                $company->bank_account_holder
            );

            return Inertia::render('Payment/Create', [
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $bookingCode,
                    'house_name' => $booking->house->name,
                    'room_number' => $booking->room->room_number ?? 'N/A',
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
            
        } catch (\Exception $e) {
            \Log::error('Payment: Error in create method', [
                'booking_id' => $bookingId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('history.index')
                ->with('error', 'Có lỗi xảy ra khi tải trang thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Confirm payment
     * Flow: Thanh toán → Trang thành công
     */
    public function confirm(Request $request, $bookingId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán.');
        }

        // Find booking
        $booking = Booking::with(['house', 'room'])->find($bookingId);
        
        if (!$booking) {
            return redirect()->route('history.index')
                ->with('error', 'Đơn đặt phòng không tồn tại.');
        }
        
        // Use PaymentService to handle payment confirmation
        $paymentService = app(PaymentService::class);
        
        // Validate payment
        $validation = $paymentService->validatePayment($booking, $user);
        if (!$validation['valid']) {
            $firstError = reset($validation['errors']);
            return back()->with('error', $firstError);
        }
        
        // If already paid, redirect to next step
        if ($booking->payment_status === 'paid') {
            if ($booking->contract_signed) {
                return redirect()->route('booking.success', $booking->id)
                    ->with('info', 'Đơn đặt phòng này đã được thanh toán và ký hợp đồng.');
            }
            return redirect()->route('contract.sign.show', ['booking' => $booking->id])
                ->with('info', 'Đơn đặt phòng này đã được thanh toán. Vui lòng ký hợp đồng.');
        }
        
        // Confirm payment using service
        try {
            $booking = $paymentService->confirmPayment($booking, $user);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        // Success - redirect to booking success page
        // Flow: Chọn phòng → Đặt phòng → Thanh toán (vừa xong) → Trang thành công
        return redirect()->route('booking.success', $booking->id)
            ->with('success', 'Thanh toán thành công! Đơn đặt phòng của bạn đã được xác nhận.');
    }

}

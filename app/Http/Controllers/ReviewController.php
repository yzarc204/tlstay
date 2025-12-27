<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\House;
use App\Models\Review;
use App\Services\FileUploadService;
use App\Services\SystemTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Store a new review for a booking.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return back()->withErrors(['error' => 'Vui lòng đăng nhập để đánh giá']);
        }

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $booking = Booking::findOrFail($request->booking_id);

        // Kiểm tra booking thuộc về user
        if ($booking->user_id !== $user->id) {
            return back()->withErrors(['error' => 'Bạn không có quyền đánh giá booking này']);
        }

        // Kiểm tra booking đã được thanh toán và không bị hủy
        if ($booking->status === 'cancelled') {
            return back()->withErrors(['error' => 'Không thể đánh giá booking đã bị hủy']);
        }

        if ($booking->payment_status !== 'paid') {
            return back()->withErrors(['error' => 'Vui lòng thanh toán booking trước khi đánh giá']);
        }

        // Kiểm tra đã có review cho booking này chưa
        if ($booking->review) {
            return back()->withErrors(['error' => 'Bạn đã đánh giá booking này rồi']);
        }

        // Kiểm tra booking đã kết thúc chưa
        $today = SystemTimeService::today();
        if ($booking->end_date->gt($today)) {
            return back()->withErrors(['error' => 'Bạn chỉ có thể đánh giá sau khi kết thúc thuê phòng']);
        }

        // Kiểm tra thời gian đánh giá: muộn nhất 14 ngày sau khi kết thúc
        $reviewDeadline = $booking->end_date->copy()->addDays(14);
        if ($today->gt($reviewDeadline)) {
            return back()->withErrors(['error' => 'Thời gian đánh giá đã hết hạn. Bạn chỉ có thể đánh giá trong vòng 14 ngày sau khi kết thúc thuê phòng']);
        }

        DB::beginTransaction();
        try {
            // Upload images
            $imagePaths = [];
            if ($request->hasFile('images')) {
                $imagePaths = $this->fileUploadService->uploadFiles(
                    $request->file('images'),
                    'reviews'
                );
            }

            // Create review
            // Rating sẽ tự động được cập nhật thông qua Model Events trong Review model
            $review = Review::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'house_id' => $booking->house_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'images' => !empty($imagePaths) ? $imagePaths : null,
            ]);

            DB::commit();

            return back()->with('success', 'Đánh giá của bạn đã được gửi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo đánh giá: ' . $e->getMessage()]);
        }
    }

}

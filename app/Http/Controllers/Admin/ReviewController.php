<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\House;
use App\Services\FileUploadService;
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
     * Display reviews for houses owned by the manager.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Review::with(['user', 'house'])
            ->whereHas('house', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })
            ->orderBy('created_at', 'desc');

        // Filter by house
        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by response status
        if ($request->filled('has_response')) {
            if ($request->has_response === '1') {
                $query->whereNotNull('manager_response');
            } else {
                $query->whereNull('manager_response');
            }
        }

        $reviews = $query->paginate(15)->withQueryString()->through(function ($review) {
            return [
                'id' => $review->id,
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                ],
                'house' => [
                    'id' => $review->house->id,
                    'name' => $review->house->name,
                ],
                'rating' => $review->rating,
                'comment' => $review->comment,
                'images' => $review->images ?? [],
                'manager_response' => $review->manager_response,
                'manager_response_at' => $review->manager_response_at?->format('Y-m-d H:i:s'),
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get houses owned by manager for filter
        $houses = \App\Models\House::where('owner_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return \Inertia\Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'houses' => $houses,
            'filters' => $request->only(['house_id', 'rating', 'has_response']),
        ]);
    }

    /**
     * Respond to a review.
     */
    public function respond(Request $request, Review $review)
    {
        $user = Auth::user();

        // Kiểm tra review thuộc về nhà của manager
        if ($review->house->owner_id !== $user->id) {
            return back()->withErrors(['error' => 'Bạn không có quyền phản hồi đánh giá này']);
        }

        $validator = Validator::make($request->all(), [
            'manager_response' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $review->update([
            'manager_response' => $request->manager_response,
            'manager_response_at' => now(),
        ]);

        return back()->with('success', 'Phản hồi đã được gửi thành công!');
    }

    /**
     * Update manager response.
     */
    public function updateResponse(Request $request, Review $review)
    {
        $user = Auth::user();

        // Kiểm tra review thuộc về nhà của manager
        if ($review->house->owner_id !== $user->id) {
            return back()->withErrors(['error' => 'Bạn không có quyền chỉnh sửa phản hồi này']);
        }

        $validator = Validator::make($request->all(), [
            'manager_response' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $review->update([
            'manager_response' => $request->manager_response,
            'manager_response_at' => now(),
        ]);

        return back()->with('success', 'Phản hồi đã được cập nhật thành công!');
    }

    /**
     * Show a single review.
     */
    public function show(Review $review)
    {
        $user = Auth::user();

        // Kiểm tra review thuộc về nhà của manager
        if ($review->house->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem đánh giá này');
        }

        $review->load(['user', 'house', 'booking']);

        return \Inertia\Inertia::render('Admin/Reviews/Show', [
            'review' => $review,
        ]);
    }

    /**
     * Delete manager response.
     */
    public function deleteResponse(Review $review)
    {
        $user = Auth::user();

        // Kiểm tra review thuộc về nhà của manager
        if ($review->house->owner_id !== $user->id) {
            return back()->withErrors(['error' => 'Bạn không có quyền xóa phản hồi này']);
        }

        $review->update([
            'manager_response' => null,
            'manager_response_at' => null,
        ]);

        return back()->with('success', 'Phản hồi đã được xóa thành công!');
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        $user = Auth::user();

        // Kiểm tra review thuộc về nhà của manager
        if ($review->house->owner_id !== $user->id) {
            return back()->withErrors(['error' => 'Bạn không có quyền xóa đánh giá này']);
        }

        DB::beginTransaction();
        try {
            $houseId = $review->house_id;
            
            // Xóa images của review
            if ($review->images && is_array($review->images)) {
                $this->fileUploadService->deleteFiles($review->images);
            }

            // Xóa review
            $review->delete();

            // Cập nhật lại rating và reviews count của house
            $this->updateHouseRating($houseId);

            DB::commit();

            return back()->with('success', 'Đánh giá đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa đánh giá: ' . $e->getMessage()]);
        }
    }

    /**
     * Update house rating and reviews count based on all reviews.
     */
    private function updateHouseRating($houseId)
    {
        $house = House::findOrFail($houseId);
        
        $reviews = Review::where('house_id', $houseId)->get();
        
        if ($reviews->count() > 0) {
            $averageRating = $reviews->avg('rating');
            $house->update([
                'rating' => round($averageRating, 2),
                'reviews' => $reviews->count(),
            ]);
        } else {
            $house->update([
                'rating' => 0,
                'reviews' => 0,
            ]);
        }
    }
}

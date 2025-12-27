<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $wishlists = Wishlist::where('user_id', $user->id)
            ->with(['house.owner'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($wishlist) {
                $house = $wishlist->house;
                return [
                    'id' => $wishlist->id,
                    'house' => [
                        'id' => $house->id,
                        'name' => $house->name,
                        'address' => $house->address,
                        'image' => $house->image ?? (is_array($house->images) && count($house->images) > 0 ? $house->images[0] : null),
                        'images' => $house->images ?? [],
                        'pricePerDay' => (float) $house->price_per_day,
                        'pricePerWeek' => $house->price_per_week ? (float) $house->price_per_week : null,
                        'pricePerMonth' => $house->price_per_month ? (float) $house->price_per_month : null,
                        'rating' => (float) ($house->rating ?? 0),
                        'reviews' => $house->reviews ?? 0,
                        'availableRooms' => $house->availableRooms,
                        'totalRooms' => $house->total_rooms,
                    ],
                    'createdAt' => $wishlist->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Wishlist', [
            'wishlists' => $wishlists,
        ]);
    }

    /**
     * Add a house to wishlist.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập để thêm vào wishlist'], 401);
        }

        $request->validate([
            'house_id' => 'required|exists:houses,id',
        ]);

        // Kiểm tra đã có trong wishlist chưa
        $existing = Wishlist::where('user_id', $user->id)
            ->where('house_id', $request->house_id)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Nhà trọ này đã có trong wishlist'], 400);
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'house_id' => $request->house_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào wishlist',
            'wishlist' => $wishlist,
        ]);
    }

    /**
     * Remove a house from wishlist.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập'], 401);
        }

        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khỏi wishlist',
        ]);
    }

    /**
     * Toggle wishlist status (add if not exists, remove if exists).
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập để thêm vào wishlist'], 401);
        }

        $request->validate([
            'house_id' => 'required|exists:houses,id',
        ]);

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('house_id', $request->house_id)
            ->first();

        if ($wishlist) {
            // Xóa nếu đã có
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'isInWishlist' => false,
                'message' => 'Đã xóa khỏi wishlist',
            ]);
        } else {
            // Thêm nếu chưa có
            $wishlist = Wishlist::create([
                'user_id' => $user->id,
                'house_id' => $request->house_id,
            ]);
            return response()->json([
                'success' => true,
                'isInWishlist' => true,
                'message' => 'Đã thêm vào wishlist',
            ]);
        }
    }

    /**
     * Check if a house is in user's wishlist.
     */
    public function check(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['isInWishlist' => false]);
        }

        $request->validate([
            'house_id' => 'required|exists:houses,id',
        ]);

        $isInWishlist = Wishlist::where('user_id', $user->id)
            ->where('house_id', $request->house_id)
            ->exists();

        return response()->json(['isInWishlist' => $isInWishlist]);
    }
}

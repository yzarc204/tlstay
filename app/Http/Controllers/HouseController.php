<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Address;
use App\Services\SystemTimeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::with(['owner', 'rooms.bookings', 'wardAddress', 'streetAddress'])
            ->withCount([
                'rooms as total_rooms_count',
            ]);

        // Filter by ward (phường)
        if ($request->filled('ward')) {
            $wardName = $request->ward;
            $query->whereHas('wardAddress', function ($q) use ($wardName) {
                $q->where('name', 'like', "%{$wardName}%");
            });
        }

        // Filter by street (đường)
        if ($request->filled('street')) {
            $streetName = $request->street;
            $query->whereHas('streetAddress', function ($q) use ($streetName) {
                $q->where('name', 'like', "%{$streetName}%");
            });
        }

        // Filter by price range
        if ($request->filled('priceRange')) {
            $priceRange = $request->priceRange;
            if (strpos($priceRange, '+') !== false) {
                // Range format: "10000000+" - Trên 10 triệu/ngày
                $minPrice = (float) str_replace('+', '', $priceRange);
                $query->where('price_per_day', '>=', $minPrice);
            } elseif (strpos($priceRange, '-') !== false) {
                // Range format: "2000000-5000000" or "0-2000000"
                [$minPrice, $maxPrice] = explode('-', $priceRange);
                $query->whereBetween('price_per_day', [(float) $minPrice, (float) $maxPrice]);
            }
        }

        $houses = $query->paginate(12)->withQueryString()
            ->through(function ($house) {
                // Calculate available rooms count based on effective status
                $availableRoomsCount = $house->rooms->filter(function ($room) {
                    return $room->getEffectiveStatus() === 'available';
                })->count();
                
                return [
                    'id' => $house->id,
                    'name' => $house->name,
                    'address' => $house->address,
                    'image' => $house->image ?? (is_array($house->images) && count($house->images) > 0 ? $house->images[0] : 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'),
                    'pricePerDay' => (float) $house->price_per_day,
                    'floors' => $house->floors,
                    'totalRooms' => $house->total_rooms_count ?? $house->total_rooms ?? 0,
                    'availableRooms' => $availableRoomsCount,
                    'rating' => (float) ($house->rating ?? 0),
                    'reviews' => $house->reviews ?? 0,
                    'amenities' => $house->amenities ?? [],
                    'ward_name' => $house->wardAddress ? $house->wardAddress->name : null,
                    'street_name' => $house->streetAddress ? $house->streetAddress->name : null,
                ];
            });

        // Get all wards and streets for filter dropdowns
        $wards = Address::where('type', 'ward')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($ward) {
                return [
                    'id' => $ward->id,
                    'name' => $ward->name,
                ];
            });

        $streets = Address::where('type', 'street')
            ->with('parent')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id'])
            ->map(function ($street) {
                return [
                    'id' => $street->id,
                    'name' => $street->name,
                    'ward_id' => $street->parent_id,
                    'ward_name' => $street->parent ? $street->parent->name : null,
                ];
            });

        return Inertia::render('HouseList', [
            'houses' => $houses,
            'filters' => $request->only(['ward', 'street', 'priceRange']),
            'wards' => $wards,
            'streets' => $streets,
        ]);
    }

    public function show($id)
    {
        $house = House::with(['owner', 'rooms.bookings', 'reviews.user'])
            ->withCount([
                'rooms as total_rooms_count',
            ])
            ->findOrFail($id);

        // Calculate available rooms count based on effective status
        $availableRoomsCount = $house->rooms->filter(function ($room) {
            return $room->getEffectiveStatus() === 'available';
        })->count();

        $rooms = $house->rooms()
            ->with('bookings') // Load bookings for status calculation
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) {
                // Use effective status based on bookings and check-in dates
                return [
                    'id' => $room->id,
                    'roomNumber' => $room->room_number,
                    'floor' => $room->floor,
                    'pricePerDay' => (float) $room->price_per_day,
                    'area' => (float) ($room->area ?? 0),
                    'status' => $room->getEffectiveStatus(), // Dynamic status based on bookings
                    'amenities' => $room->amenities ?? [],
                    'images' => $room->images ?? [],
                ];
            })
            ->values();

        // Determine default image: prioritize featured_images, then images, then image field
        $defaultImage = 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800';
        if ($house->featured_images && is_array($house->featured_images) && count($house->featured_images) > 0) {
            $defaultImage = $house->featured_images[0];
        } elseif ($house->images && is_array($house->images) && count($house->images) > 0) {
            $defaultImage = $house->images[0];
        } elseif ($house->image) {
            $defaultImage = $house->image;
        }

        // Get reviews with user info
        $reviews = $house->reviews()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        'avatar' => $review->user->avatar,
                    ],
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'images' => $review->images ?? [],
                    'manager_response' => $review->manager_response,
                    'manager_response_at' => $review->manager_response_at?->format('Y-m-d H:i:s'),
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Check if current user can review this house
        $canReview = false;
        $reviewableBooking = null;
        if (auth()->check()) {
            $user = auth()->user();
            
            // Đếm số lần đã thuê (bookings đã thanh toán và không bị hủy)
            $totalBookings = \App\Models\Booking::where('user_id', $user->id)
                ->where('house_id', $house->id)
                ->where('payment_status', 'paid')
                ->where('status', '!=', 'cancelled')
                ->count();
            
            // Đếm số lần đã đánh giá
            $totalReviews = \App\Models\Review::where('user_id', $user->id)
                ->where('house_id', $house->id)
                ->count();
            
            // Kiểm tra điều kiện:
            // 1. Phải có ít nhất 1 booking đã thanh toán
            // 2. Số lần đánh giá phải nhỏ hơn số lần thuê
            // 3. Phải có booking chưa được đánh giá
            // 4. Booking phải đã kết thúc và trong vòng 14 ngày sau khi kết thúc
            if ($totalBookings > 0 && $totalReviews < $totalBookings) {
                $today = SystemTimeService::today();
                
                // Find a booking that's paid, not cancelled, doesn't have a review,
                // has ended, and is within 14 days after end date
                $reviewableBooking = \App\Models\Booking::where('user_id', $user->id)
                    ->where('house_id', $house->id)
                    ->where('payment_status', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->whereDoesntHave('review')
                    ->where('end_date', '<=', $today) // Booking đã kết thúc
                    ->whereRaw('DATE_ADD(end_date, INTERVAL 14 DAY) >= ?', [$today]) // Trong vòng 14 ngày sau khi kết thúc
                    ->first();
                
                $canReview = $reviewableBooking !== null;
            }
        }

        $houseData = [
            'id' => $house->id,
            'name' => $house->name,
            'address' => $house->address,
            'description' => $house->description,
            'image' => $defaultImage,
            'images' => $house->images ?? [],
            'featured_images' => $house->featured_images ?? [],
            'pricePerDay' => (float) $house->price_per_day,
            'floors' => $house->floors,
            'totalRooms' => $house->total_rooms_count ?? $house->total_rooms ?? 0,
            'availableRooms' => $availableRoomsCount,
            'rating' => (float) ($house->rating ?? 0),
            'reviews' => $house->reviews ?? 0,
            'amenities' => $house->amenities ?? [],
            'latitude' => $house->latitude ? (float) $house->latitude : null,
            'longitude' => $house->longitude ? (float) $house->longitude : null,
            'owner' => $house->owner ? [
                'id' => $house->owner->id,
                'name' => $house->owner->name,
                'email' => $house->owner->email,
                'phone' => $house->owner->phone,
            ] : null,
        ];

        return Inertia::render('HouseDetail', [
            'house' => $houseData,
            'rooms' => $rooms,
            'reviews' => $reviews,
            'canReview' => $canReview,
            'reviewableBookingId' => $reviewableBooking?->id,
        ]);
    }
}

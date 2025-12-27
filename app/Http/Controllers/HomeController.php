<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Banner;
use App\Models\Address;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy 6 nhà trọ nổi bật, sắp xếp theo rating, ưu tiên nhà trọ có phòng trống
        $featuredHouses = House::with(['owner'])
            ->withCount([
                'rooms as available_rooms_count' => function ($query) {
                    $query->where('status', 'available');
                },
                'rooms as total_rooms_count',
            ])
            ->get()
            ->sortByDesc(function ($house) {
                // Ưu tiên nhà trọ có phòng trống, sau đó sắp xếp theo rating
                $hasAvailableRooms = ($house->available_rooms_count ?? 0) > 0 ? 1000 : 0;
                $rating = (float) ($house->rating ?? 0);
                return $hasAvailableRooms + $rating;
            })
            ->take(6)
            ->map(function ($house) {
                return [
                    'id' => $house->id,
                    'name' => $house->name,
                    'address' => $house->address,
                    'image' => $house->image ?? (is_array($house->images) && count($house->images) > 0 ? $house->images[0] : 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'),
                    'pricePerDay' => (float) $house->price_per_day,
                    'pricePerWeek' => $house->price_per_week ? (float) $house->price_per_week : null,
                    'pricePerMonth' => $house->price_per_month ? (float) $house->price_per_month : null,
                    'floors' => $house->floors,
                    'totalRooms' => $house->total_rooms_count ?? $house->total_rooms ?? 0,
                    'availableRooms' => $house->available_rooms_count ?? 0,
                    'rating' => (float) ($house->rating ?? 0),
                    'reviews' => $house->reviews ?? 0,
                    'amenities' => $house->amenities ?? [],
                ];
            })
            ->values();

        // Get active sliders
        $sliders = Banner::getActive()->map(function ($slider) {
            return [
                'id' => $slider->id,
                'title' => $slider->title,
                'description' => $slider->description,
                'image' => $slider->image,
                'link' => $slider->link,
                'show_text' => $slider->show_text,
                'text_title' => $slider->text_title,
                'text_subtitle' => $slider->text_subtitle,
                'text_line1' => $slider->text_line1,
                'text_line2' => $slider->text_line2,
                'text_line3' => $slider->text_line3,
                'text_position' => $slider->text_position,
            ];
        });

        // Get all wards and streets for search dropdowns
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

        return Inertia::render('Home', [
            'featuredHouses' => $featuredHouses,
            'sliders' => $sliders,
            'wards' => $wards,
            'streets' => $streets,
        ]);
    }
}

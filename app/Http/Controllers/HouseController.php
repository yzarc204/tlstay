<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HouseController extends Controller
{
    public function index()
    {
        $houses = House::with(['owner', 'rooms.bookings'])
            ->withCount([
                'rooms as total_rooms_count',
            ])
            ->get()
            ->map(function ($house) {
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
                ];
            })
            ->values();

        return Inertia::render('HouseList', [
            'houses' => $houses,
        ]);
    }

    public function show($id)
    {
        $house = House::with(['owner', 'rooms.bookings'])
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
            'contactPhone' => $house->contact_phone,
            'contactEmail' => $house->contact_email,
        ];

        return Inertia::render('HouseDetail', [
            'house' => $houseData,
            'rooms' => $rooms,
        ]);
    }
}

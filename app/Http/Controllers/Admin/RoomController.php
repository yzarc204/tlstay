<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Models\House;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request, House $house): RedirectResponse
    {
        $validated = $request->validated();
        $validated['house_id'] = $house->id;

        // If status is not occupied, clear tenant_id and rental dates
        if (!isset($validated['status']) || $validated['status'] !== 'occupied') {
            $validated['tenant_id'] = null;
            $validated['tenant_name'] = null;
            $validated['rental_start_date'] = null;
            $validated['rental_end_date'] = null;
        } else {
            // If tenant_id is provided, get tenant name from user
            if (isset($validated['tenant_id']) && $validated['tenant_id']) {
                $tenant = \App\Models\User::find($validated['tenant_id']);
                if ($tenant) {
                    $validated['tenant_name'] = $tenant->name;
                }
            }
        }

        // Inherit price from house if not provided or empty
        if (!isset($validated['price_per_day']) || $validated['price_per_day'] === '' || $validated['price_per_day'] === null) {
            $validated['price_per_day'] = $house->price_per_day;
        }
        
        // Always inherit amenities from house when creating a new room
        // If amenities array is empty or not provided, use house amenities
        if (!isset($validated['amenities']) || !is_array($validated['amenities']) || empty($validated['amenities'])) {
            $validated['amenities'] = $house->amenities ?? [];
        }

        // Process images if provided
        // Images can be either uploaded files or URLs from house images
        if ($request->hasFile('images')) {
            // Handle file uploads (legacy support)
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
            $validated['images'] = $imagePaths;
        } elseif ($request->has('images') && is_array($request->input('images'))) {
            // Handle image URLs selected from house images
            $imageUrls = array_filter($request->input('images'), function ($img) {
                return !empty($img) && is_string($img);
            });
            
            // Validate that all images belong to the house
            $houseImages = $house->images ?? [];
            if (is_array($houseImages)) {
                $validImageUrls = array_intersect($imageUrls, $houseImages);
                $validated['images'] = array_values($validImageUrls);
            } else {
                $validated['images'] = [];
            }
        }

        // Process amenities
        if (isset($validated['amenities']) && is_array($validated['amenities'])) {
            $validated['amenities'] = array_filter($validated['amenities']);
        }

        Room::create($validated);

        // Auto-update total_rooms count
        $house->update([
            'total_rooms' => $house->rooms()->count()
        ]);

        return back()->with('success', 'Đã thêm phòng thành công.');
    }

    /**
     * Update the specified room.
     */
    public function update(StoreRoomRequest $request, House $house, Room $room): RedirectResponse
    {
        $validated = $request->validated();

        // If status is not occupied, clear tenant_id and rental dates
        if ($validated['status'] !== 'occupied') {
            $validated['tenant_id'] = null;
            $validated['tenant_name'] = null;
            $validated['rental_start_date'] = null;
            $validated['rental_end_date'] = null;
        } else {
            // If tenant_id is provided, get tenant name from user
            if (isset($validated['tenant_id']) && $validated['tenant_id']) {
                $tenant = \App\Models\User::find($validated['tenant_id']);
                if ($tenant) {
                    $validated['tenant_name'] = $tenant->name;
                }
            }
        }

        // Process images if provided
        // Images can be either uploaded files or URLs from house images
        if ($request->hasFile('images')) {
            // Handle file uploads (legacy support)
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('rooms', 'public');
                $imagePaths[] = '/storage/' . $path;
            }
            $validated['images'] = $imagePaths;
        } elseif ($request->has('images') && is_array($request->input('images'))) {
            // Handle image URLs selected from house images
            $imageUrls = array_filter($request->input('images'), function ($img) {
                return !empty($img) && is_string($img);
            });
            
            // Validate that all images belong to the house
            $houseImages = $house->images ?? [];
            if (is_array($houseImages)) {
                $validImageUrls = array_intersect($imageUrls, $houseImages);
                $validated['images'] = array_values($validImageUrls);
            } else {
                $validated['images'] = [];
            }
        }

        // Process amenities
        if (isset($validated['amenities']) && is_array($validated['amenities'])) {
            $validated['amenities'] = array_filter($validated['amenities']);
        }

        $room->update($validated);

        return back()->with('success', 'Đã cập nhật phòng thành công.');
    }

    /**
     * Remove the specified room.
     */
    public function destroy(House $house, Room $room): RedirectResponse
    {
        $room->delete();

        // Auto-update total_rooms count
        $house->update([
            'total_rooms' => $house->rooms()->count()
        ]);

        return back()->with('success', 'Đã xóa phòng thành công.');
    }
}

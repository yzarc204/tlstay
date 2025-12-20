<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHouseRequest;
use App\Http\Traits\HandlesFileUploads;
use App\Models\House;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseController extends Controller
{
    use HandlesFileUploads;
    /**
     * Display a listing of houses.
     */
    public function index(Request $request): Response
    {
        $query = House::with(['owner'])
            ->withCount([
                'rooms',
                'rooms as available_rooms_count' => function ($query) {
                    $query->where('status', 'available');
                },
                'rooms as occupied_rooms_count' => function ($query) {
                    $query->where('status', 'occupied');
                },
            ])
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by ward
        if ($request->filled('ward_id')) {
            $query->where('ward_id', $request->ward_id);
        }

        // Filter by street
        if ($request->filled('street_id')) {
            $query->where('street_id', $request->street_id);
        }

        $houses = $query->paginate(12)->withQueryString();

        // Get all wards and streets for filters
        $wards = \App\Models\Address::where('type', 'ward')
            ->orderBy('name')
            ->get(['id', 'name']);

        $streets = \App\Models\Address::where('type', 'street')
            ->with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Admin/Houses/Index', [
            'houses' => $houses,
            'filters' => $request->only(['search', 'ward_id', 'street_id']),
            'wards' => $wards,
            'streets' => $streets,
        ]);
    }

    /**
     * Show the form for creating a new house.
     */
    public function create(): Response
    {
        // Get all streets for the select box
        $streets = \App\Models\Address::where('type', 'street')
            ->with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Admin/Houses/Create', [
            'streets' => $streets,
        ]);
    }

    /**
     * Store a newly created house.
     */
    public function store(StoreHouseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Set owner to current authenticated manager
        $validated['owner_id'] = auth()->id();

        // Set default floors if not provided
        if (!isset($validated['floors'])) {
            $validated['floors'] = 1;
        }

        // Get ward_id from street's parent and build full address
        if (isset($validated['street_id']) && isset($validated['street_detail'])) {
            $street = \App\Models\Address::with('parent')->find($validated['street_id']);
            if ($street) {
                // Set ward_id from street's parent
                if ($street->parent_id) {
                    $validated['ward_id'] = $street->parent_id;
                }

                // Build full address: street_detail + street name + ward name
                $addressParts = [$validated['street_detail'], $street->name];
                if ($street->parent) {
                    $addressParts[] = $street->parent->name;
                }
                $validated['address'] = implode(', ', $addressParts);
            }
        }

        // Set total_rooms to 0 initially (will be auto-updated when rooms are added)
        $validated['total_rooms'] = 0;

        // Process images if provided
        $imagePaths = [];
        if ($request->hasFile('images')) {
            $imagePaths = $this->uploadFiles($request->file('images'), 'houses');
            $validated['images'] = $imagePaths;

            // Set first image as main image
            if (!empty($imagePaths)) {
                $validated['image'] = $imagePaths[0];
            }
        }

        // Process featured images (selected images to display in detail page)
        // If featured_image_indices is provided, map indices to actual image URLs
        if ($request->has('featured_image_indices') && is_array($request->input('featured_image_indices')) && !empty($imagePaths)) {
            $indices = array_map('intval', $request->input('featured_image_indices'));
            $featuredImagePaths = [];
            foreach ($indices as $index) {
                if (isset($imagePaths[$index])) {
                    $featuredImagePaths[] = $imagePaths[$index];
                }
            }
            $validated['featured_images'] = $featuredImagePaths;
        } elseif ($request->has('featured_images') && is_array($request->input('featured_images'))) {
            $featuredImages = array_filter($request->input('featured_images'), function ($img) {
                return !empty($img);
            });
            $validated['featured_images'] = array_values($featuredImages);
        } elseif (!empty($imagePaths)) {
            // If no featured_images selected but images uploaded, use all uploaded images as featured
            $validated['featured_images'] = $imagePaths;
        }

        // Process amenities
        if (isset($validated['amenities']) && is_array($validated['amenities'])) {
            $validated['amenities'] = array_filter($validated['amenities']);
        }

        $house = House::create($validated);

        return redirect()
            ->route('admin.houses.index')
            ->with('success', 'Đã thêm nhà trọ thành công.');
    }

    /**
     * Display the specified house.
     */
    public function show(House $house): Response
    {
        $house->load(['owner', 'rooms']);

        return Inertia::render('Admin/Houses/Show', [
            'house' => $house,
        ]);
    }

    /**
     * Show the room management page with diagram.
     */
    public function manage(House $house): Response
    {
        $house->load([
            'rooms' => function ($query) {
                $query->with(['tenant', 'bookings' => function ($bookingQuery) {
                    $bookingQuery->where('status', 'active')
                        ->where('payment_status', 'paid')
                        ->orderBy('created_at', 'desc');
                }])->orderBy('floor')->orderBy('room_number');
            }
        ]);

        // Group rooms by floor
        $roomsByFloor = $house->rooms->groupBy('floor')->toArray();

        // Get list of users (customers only) for tenant selection
        $users = \App\Models\User::where('role', 'customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('Admin/Houses/Manage', [
            'house' => $house,
            'roomsByFloor' => $roomsByFloor,
            'users' => $users,
        ]);
    }

    /**
     * Add a new floor to the house.
     */
    public function addFloor(House $house): RedirectResponse
    {
        $house->increment('floors');

        return back()->with('success', 'Đã thêm tầng mới thành công.');
    }

    /**
     * Remove a floor from the house.
     */
    public function removeFloor(House $house, Request $request): RedirectResponse
    {
        $request->validate([
            'floor' => ['required', 'integer', 'min:1', 'max:' . $house->floors],
        ]);

        $floor = (int) $request->input('floor');

        // Check if floor has rooms
        $roomsOnFloor = $house->rooms()->where('floor', $floor)->count();
        if ($roomsOnFloor > 0) {
            return back()->withErrors(['floor' => 'Không thể xóa tầng có phòng. Vui lòng xóa hoặc di chuyển các phòng trước.']);
        }

        // Check if it's the last floor
        if ($house->floors <= 1) {
            return back()->withErrors(['floor' => 'Không thể xóa tầng cuối cùng.']);
        }

        // Move rooms from higher floors down if needed
        $house->rooms()->where('floor', '>', $floor)->decrement('floor');

        $house->decrement('floors');

        return back()->with('success', 'Đã xóa tầng thành công.');
    }

    /**
     * Show the form for editing the specified house.
     */
    public function edit(House $house): Response
    {
        $house->load(['owner', 'rooms']);

        // Ensure images array is properly formatted and filter out empty values
        if ($house->images && is_array($house->images)) {
            $house->images = array_filter($house->images, function ($img) {
                return !empty($img) && trim($img) !== '';
            });
            $house->images = array_values($house->images); // Re-index array
        } elseif ($house->image && trim($house->image) !== '') {
            $house->images = [$house->image];
        } else {
            $house->images = [];
        }

        // Ensure featured_images array is properly formatted
        if ($house->featured_images && is_array($house->featured_images)) {
            $house->featured_images = array_filter($house->featured_images, function ($img) {
                return !empty($img) && trim($img) !== '';
            });
            $house->featured_images = array_values($house->featured_images);
        } else {
            $house->featured_images = [];
        }

        // Get all streets for the select box
        $streets = \App\Models\Address::where('type', 'street')
            ->with('parent:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return Inertia::render('Admin/Houses/Edit', [
            'house' => $house,
            'streets' => $streets,
        ]);
    }

    /**
     * Update the specified house.
     */
    public function update(StoreHouseRequest $request, House $house): RedirectResponse
    {
        $validated = $request->validated();

        // Remove total_rooms from validated data - it's auto-updated when rooms are added/removed
        unset($validated['total_rooms']);

        // Process images: merge existing images with new ones
        $allImages = [];
        $newImagePaths = [];
        $hasImageChanges = false;

        // Get existing images that should be kept
        if ($request->has('existing_images') && is_array($request->input('existing_images'))) {
            $existingImages = array_filter($request->input('existing_images'), function ($img) {
                return !empty($img) && is_string($img);
            });
            $allImages = array_merge($allImages, $existingImages);
            $hasImageChanges = true;
        }

        // Process new images if provided
        if ($request->hasFile('images')) {
            $newImagePaths = $this->uploadFiles($request->file('images'), 'houses');
            $allImages = array_merge($allImages, $newImagePaths);
            $hasImageChanges = true;
        }

        // Get ward_id from street's parent and build full address
        if (isset($validated['street_id']) && isset($validated['street_detail'])) {
            $street = \App\Models\Address::with('parent')->find($validated['street_id']);
            if ($street) {
                // Set ward_id from street's parent
                if ($street->parent_id) {
                    $validated['ward_id'] = $street->parent_id;
                }

                // Build full address: street_detail + street name + ward name
                $addressParts = [$validated['street_detail'], $street->name];
                if ($street->parent) {
                    $addressParts[] = $street->parent->name;
                }
                $validated['address'] = implode(', ', $addressParts);
            }
        }

        // Update images only if we have changes
        // If existing_images is provided or new images are uploaded, update images
        if ($hasImageChanges) {
            $validated['images'] = $allImages;
            // Set first image as main image if we have any images
            if (!empty($allImages)) {
                $validated['image'] = $allImages[0];
            } else {
                $validated['image'] = null;
            }
        } else {
            // If no image changes, use current images for featured_images processing
            $allImages = $house->images ?? [];
            if (!is_array($allImages)) {
                $allImages = $house->image ? [$house->image] : [];
            }
        }

        // Process featured images (selected images to display in detail page)
        // Always process featured_images if provided, even if images haven't changed
        $featuredImagePaths = [];

        // First, collect featured images from existing images (URLs)
        if ($request->has('featured_images') && is_array($request->input('featured_images'))) {
            $featuredImageUrls = array_filter($request->input('featured_images'), function ($img) {
                return !empty($img) && is_string($img);
            });
            // Only include featured images that exist in the allImages array
            $featuredImagePaths = array_values(array_intersect($featuredImageUrls, $allImages));
        }

        // Then, add featured images from new images (indices mapped to URLs)
        if ($request->has('featured_image_indices') && is_array($request->input('featured_image_indices')) && !empty($newImagePaths)) {
            $indices = array_map('intval', $request->input('featured_image_indices'));
            // Map indices to actual new image URLs
            foreach ($indices as $index) {
                if (isset($newImagePaths[$index])) {
                    $featuredImagePaths[] = $newImagePaths[$index];
                }
            }
        }

        // Remove duplicates and set featured images
        $featuredImagePaths = array_values(array_unique($featuredImagePaths));

        // If featured_images were provided (even if empty array), update them
        // Otherwise, if no featured images selected but images exist, use all images as featured
        if ($request->has('featured_images') || $request->has('featured_image_indices')) {
            if (!empty($featuredImagePaths)) {
                $validated['featured_images'] = $featuredImagePaths;
            } else {
                // If explicitly set to empty, set to empty array
                // Otherwise, if images exist, use all images as featured
                if (!empty($allImages)) {
                    $validated['featured_images'] = $allImages;
                } else {
                    $validated['featured_images'] = [];
                }
            }
        } elseif (!empty($allImages) && empty($house->featured_images)) {
            // If no featured_images provided and house has no featured_images, use all images
            $validated['featured_images'] = $allImages;
        }

        // Process amenities
        if (isset($validated['amenities']) && is_array($validated['amenities'])) {
            $validated['amenities'] = array_filter($validated['amenities']);
        }

        $house->update($validated);

        return redirect()
            ->route('admin.houses.index')
            ->with('success', 'Đã cập nhật nhà trọ thành công.');
    }

    /**
     * Remove the specified house.
     * 
     * Note: This will delete the house even if it has tenants or occupied rooms.
     * All related rooms, bookings, and tenant associations will be cascade deleted
     * according to database foreign key constraints.
     */
    public function destroy(House $house): RedirectResponse
    {
        $house->delete();

        return redirect()
            ->route('admin.houses.index')
            ->with('success', 'Đã xóa nhà trọ thành công.');
    }
}

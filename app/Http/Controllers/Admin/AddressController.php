<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAddressRequest;
use App\Http\Requests\Admin\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    /**
     * Display a listing of addresses.
     */
    public function index(Request $request): Response
    {
        // Get all wards with their streets
        $wardsQuery = Address::where('type', 'ward')
            ->with(['children' => function ($query) {
                $query->orderBy('name');
            }])
            ->withCount('children')
            ->orderBy('name');

        // Search - search in both ward names and street names
        if ($request->filled('search')) {
            $search = $request->search;
            $wardsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by specific ward
        if ($request->filled('parent_id')) {
            $wardsQuery->where('id', $request->parent_id);
        }

        $wards = $wardsQuery->get();

        // Get parent addresses for filter (wards for streets)
        $parentAddresses = Address::where('type', 'ward')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Addresses/Index', [
            'wards' => $wards,
            'parentAddresses' => $parentAddresses,
            'filters' => $request->only(['search', 'parent_id']),
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(StoreAddressRequest $request)
    {
        $validated = $request->validated();

        // Validate parent_id based on type
        // Ward doesn't need parent, street needs ward as parent
        if ($validated['type'] === 'street' && empty($validated['parent_id'])) {
            return back()->withErrors(['parent_id' => 'Vui lòng chọn phường/xã.']);
        }

        // Validate parent type matches (street must have ward as parent)
        if ($validated['type'] === 'street' && !empty($validated['parent_id'])) {
            $parent = Address::find($validated['parent_id']);
            if (!$parent || $parent->type !== 'ward') {
                return back()->withErrors(['parent_id' => 'Địa điểm cha phải là phường/xã.']);
            }
        }
        
        // Ward should not have parent
        if ($validated['type'] === 'ward' && !empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        $address = Address::create($validated);

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', 'Đã thêm địa điểm thành công.');
    }

    /**
     * Update the specified address.
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        $validated = $request->validated();

        // Validate parent_id based on type
        // Ward doesn't need parent, street needs ward as parent
        if ($validated['type'] === 'street' && empty($validated['parent_id'])) {
            return back()->withErrors(['parent_id' => 'Vui lòng chọn phường/xã.']);
        }

        // Cannot set itself as parent
        if (!empty($validated['parent_id']) && $validated['parent_id'] == $address->id) {
            return back()->withErrors(['parent_id' => 'Không thể chọn chính địa điểm này làm địa điểm cha.']);
        }

        // Validate parent type matches (street must have ward as parent)
        if ($validated['type'] === 'street' && !empty($validated['parent_id'])) {
            $parent = Address::find($validated['parent_id']);
            if (!$parent || $parent->type !== 'ward') {
                return back()->withErrors(['parent_id' => 'Địa điểm cha phải là phường/xã.']);
            }
        }
        
        // Ward should not have parent
        if ($validated['type'] === 'ward' && !empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }

        // Cannot change type if has children
        if ($address->type !== $validated['type'] && $address->children()->count() > 0) {
            return back()->withErrors(['type' => 'Không thể thay đổi loại địa điểm khi đã có địa điểm con.']);
        }

        $address->update($validated);

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', 'Đã cập nhật địa điểm thành công.');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        // Cannot delete if has children
        if ($address->children()->count() > 0) {
            return back()->withErrors(['error' => 'Không thể xóa địa điểm khi đã có địa điểm con.']);
        }

        // Check if address is used in houses
        $housesCount = \App\Models\House::where(function ($query) use ($address) {
            $query->where('ward_id', $address->id)
                ->orWhere('street_id', $address->id);
        })->count();

        if ($housesCount > 0) {
            return back()->withErrors(['error' => "Không thể xóa địa điểm vì đang được sử dụng bởi {$housesCount} nhà trọ."]);
        }

        $address->delete();

        return redirect()
            ->route('admin.addresses.index')
            ->with('success', 'Đã xóa địa điểm thành công.');
    }

    /**
     * Get addresses by type and parent (for cascading selects).
     */
    public function getByParent(Request $request)
    {
        $type = $request->input('type');
        $parentId = $request->input('parent_id');

        $query = Address::where('type', $type)
            ->orderBy('name');

        // For wards, always get those without parent
        if ($type === 'ward') {
            $query->whereNull('parent_id');
        } 
        // For streets, filter by parent_id (ward)
        elseif ($type === 'street') {
            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }
        }

        return response()->json($query->get(['id', 'name']));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Helpers\CodeGenerator;
use App\Models\Booking;
use App\Models\House;
use App\Models\Invoice;
use App\Models\Room;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Store a newly created room.
     */
    public function store(StoreRoomRequest $request, House $house): RedirectResponse
    {
        $validated = $request->validated();
        $validated['house_id'] = $house->id;

        // If status is not active, clear tenant_id and rental dates
        if (!isset($validated['status']) || $validated['status'] !== 'active') {
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

        // Inherit prices from house if not provided or empty
        if (!isset($validated['price_per_day']) || $validated['price_per_day'] === '' || $validated['price_per_day'] === null) {
            $validated['price_per_day'] = $house->price_per_day;
        }
        if (!isset($validated['price_per_week']) || $validated['price_per_week'] === '' || $validated['price_per_week'] === null) {
            $validated['price_per_week'] = $house->price_per_week;
        }
        if (!isset($validated['price_per_month']) || $validated['price_per_month'] === '' || $validated['price_per_month'] === null) {
            $validated['price_per_month'] = $house->price_per_month;
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
            $imagePaths = $this->fileUploadService->uploadFiles($request->file('images'), 'rooms');
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

        // Store old tenant_id to check if it changed
        $oldTenantId = $room->tenant_id;
        $oldRentalStartDate = $room->rental_start_date;
        $oldRentalEndDate = $room->rental_end_date;

        // If status is not active, clear tenant_id and rental dates
        if ($validated['status'] !== 'active') {
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
            $imagePaths = $this->fileUploadService->uploadFiles($request->file('images'), 'rooms');
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

        // Update room
        $room->update($validated);

        // Check if tenant information changed and create booking if needed
        $tenantChanged = ($oldTenantId != $validated['tenant_id']) ||
            ($oldRentalStartDate != $validated['rental_start_date']) ||
            ($oldRentalEndDate != $validated['rental_end_date']);

        if (
            $tenantChanged &&
            $validated['status'] === 'active' &&
            isset($validated['tenant_id']) &&
            $validated['tenant_id'] &&
            isset($validated['rental_start_date']) &&
            $validated['rental_start_date'] &&
            isset($validated['rental_end_date']) &&
            $validated['rental_end_date']
        ) {

            // Create booking with 0 price and paid status
            DB::transaction(function () use ($room, $house, $validated) {
                $tenant = \App\Models\User::find($validated['tenant_id']);

                if ($tenant) {
                    // Create booking
                    // Generate unique booking code
                    $bookingCode = CodeGenerator::generateBookingCode();

                    $booking = Booking::create([
                        'user_id' => $tenant->id,
                        'house_id' => $house->id,
                        'room_id' => $room->id,
                        'start_date' => $validated['rental_start_date'],
                        'end_date' => $validated['rental_end_date'],
                        'total_price' => 0,
                        'discount_amount' => 0,
                        'tenant_name' => $validated['tenant_name'] ?? $tenant->name,
                        'status' => 'active',
                        'payment_status' => 'paid',
                        'payment_method' => 'manual',
                        'paid_at' => now(),
                        'booking_code' => $bookingCode,
                    ]);
                }
            });
        }

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

    /**
     * Create invoice for a room (electricity, water, other fees).
     */
    public function createInvoice(Request $request, House $house, Room $room): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'electricity_amount' => 'required|numeric|min:0',
            'water_amount' => 'required|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if room has tenant
        if (!$room->tenant_id) {
            return back()->withErrors(['tenant_id' => 'Phòng này không có khách thuê.'])->withInput();
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Verify date range is within tenant rental period
        if ($room->rental_start_date && $room->rental_end_date) {
            if ($startDate->lt($room->rental_start_date) || $endDate->gt($room->rental_end_date)) {
                return back()->withErrors(['start_date' => 'Khoảng thời gian tính hóa đơn phải nằm trong thời gian thuê phòng.'])->withInput();
            }
        }

        // Find active booking for this room and tenant
        $booking = Booking::where('room_id', $room->id)
            ->where('user_id', $room->tenant_id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                });
            })
            ->first();

        // Check if invoice already exists with overlapping date range
        $query = Invoice::where('user_id', $room->tenant_id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($overlapQuery) use ($startDate, $endDate) {
                    $overlapQuery->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                });
            });

        if ($booking) {
            $query->where('booking_id', $booking->id);
        } else {
            $query->whereNull('booking_id');
        }

        $existingInvoice = $query->first();

        if ($existingInvoice) {
            return back()->withErrors(['start_date' => 'Đã tồn tại hóa đơn trong khoảng thời gian này.'])->withInput();
        }

        // Calculate total amount (electricity + water + other fees)
        // Note: amount field in Invoice model represents room rent, but for utility invoices it's 0
        // The total utility cost is electricity_amount + water_amount + other_fees
        $totalAmount = (float) $request->electricity_amount
            + (float) $request->water_amount
            + (float) ($request->other_fees ?? 0);

        // Generate unique invoice code
        $invoiceCode = CodeGenerator::generateInvoiceCode();

        // Create invoice
        $invoice = Invoice::create([
            'booking_id' => $booking ? $booking->id : null,
            'user_id' => $room->tenant_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $startDate->month,
            'year' => $startDate->year,
            'amount' => 0, // Room rent is 0 for utility invoices (only electricity/water/other fees)
            'electricity_amount' => $request->electricity_amount,
            'water_amount' => $request->water_amount,
            'other_fees' => $request->other_fees ?? 0,
            'status' => 'pending',
            'due_date' => $request->due_date ? Carbon::parse($request->due_date) : Carbon::now()->addDays(7),
            'notes' => $request->notes,
            'invoice_code' => $invoiceCode,
        ]);

        return back()->with('success', 'Tạo hóa đơn điện nước thành công!');
    }

    /**
     * Get invoices for a room.
     */
    public function getInvoices(House $house, Room $room): \Illuminate\Http\JsonResponse
    {
        if (!$room->tenant_id) {
            return response()->json(['invoices' => []]);
        }

        // Find active booking for this room and tenant
        $booking = Booking::where('room_id', $room->id)
            ->where('user_id', $room->tenant_id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->first();

        // Get invoices for this tenant and room
        $query = Invoice::where('user_id', $room->tenant_id)
            ->orderBy('start_date', 'desc')
            ->orderBy('end_date', 'desc');

        if ($booking) {
            $query->where(function ($q) use ($booking) {
                $q->where('booking_id', $booking->id)
                    ->orWhereNull('booking_id');
            });
        } else {
            $query->whereNull('booking_id');
        }

        $invoices = $query->get()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                'electricity_amount' => (float) $invoice->electricity_amount,
                'water_amount' => (float) $invoice->water_amount,
                'other_fees' => (float) ($invoice->other_fees ?? 0),
                'total' => (float) (
                    $invoice->electricity_amount +
                    $invoice->water_amount +
                    ($invoice->other_fees ?? 0)
                ),
                'status' => $invoice->status,
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json(['invoices' => $invoices]);
    }
}

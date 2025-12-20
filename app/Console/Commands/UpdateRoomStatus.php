<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use App\Services\SystemTimeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRoomStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update room status: remove expired tenants and assign new tenants for check-in date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Use system time (real or manual) instead of real time
        $today = SystemTimeService::today();
        $processedRooms = 0;
        $expiredTenants = 0;
        $newCheckIns = 0;
        $expiredRoomsList = [];
        $checkInRoomsList = [];

        $timeInfo = SystemTimeService::getTimeInfo();
        if ($timeInfo['is_manual']) {
            $this->info("Using manual system time: {$timeInfo['current_datetime']}");
        }
        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Remove expired tenants (rental_end_date < today)
            $this->info("=== Bước 1: Xử lý phòng hết hạn ===");
            $expiredRooms = Room::where('status', 'occupied')
                ->whereNotNull('rental_end_date')
                ->where('rental_end_date', '<', $today)
                ->with(['house', 'tenant'])
                ->get();

            if ($expiredRooms->count() > 0) {
                $this->info("Tìm thấy {$expiredRooms->count()} phòng hết hạn. Đang xử lý...");
                $this->newLine();
            } else {
                $this->info("Không có phòng nào hết hạn.");
                $this->newLine();
            }

            foreach ($expiredRooms as $room) {
                $expiredDate = $room->rental_end_date?->format('Y-m-d');
                $tenantId = $room->tenant_id;
                $tenantName = $room->tenant_name;
                $houseName = $room->house->name ?? 'N/A';

                // Display step-by-step info
                $this->line("  → Phòng <fg=cyan>{$houseName} - Phòng {$room->room_number} (Tầng {$room->floor})</>");
                $this->line("    Khách: <fg=yellow>{$tenantName}</>");
                $this->line("    Ngày hết hạn: <fg=red>{$expiredDate}</>");
                $this->line("    <fg=green>✓ Đã xóa khách, chuyển phòng về trạng thái trống</>");
                $this->newLine();

                $room->update([
                    'status' => 'available',
                    'tenant_id' => null,
                    'tenant_name' => null,
                    'rental_start_date' => null,
                    'rental_end_date' => null,
                ]);
                $expiredTenants++;
                $processedRooms++;

                // Collect info for display
                $expiredRoomsList[] = [
                    'house_name' => $houseName,
                    'room_number' => $room->room_number,
                    'floor' => $room->floor,
                    'tenant_name' => $tenantName ?? 'N/A',
                    'expired_date' => $expiredDate,
                ];

                Log::info('Removed expired tenant from room', [
                    'room_id' => $room->id,
                    'house_id' => $room->house_id,
                    'room_number' => $room->room_number,
                    'tenant_id' => $tenantId,
                    'expired_date' => $expiredDate,
                ]);
            }

            // Step 2: Assign new tenants for check-in date (start_date = today and payment_status = 'paid')
            $this->info("=== Bước 2: Xử lý phòng có khách chuyển vào ===");
            $checkInBookings = Booking::where('payment_status', 'paid')
                ->where('status', 'active')
                ->whereDate('start_date', $today)
                ->with(['room', 'user'])
                ->get();

            if ($checkInBookings->count() > 0) {
                $this->info("Tìm thấy {$checkInBookings->count()} booking cần chuyển vào phòng. Đang xử lý...");
                $this->newLine();
            } else {
                $this->info("Không có booking nào cần chuyển vào phòng.");
                $this->newLine();
            }

            foreach ($checkInBookings as $booking) {
                $room = $booking->room;

                if (!$room) {
                    $this->warn("  ⚠ Booking #{$booking->id} không có phòng. Bỏ qua.");
                    $this->newLine();
                    Log::warning('Booking has no room', ['booking_id' => $booking->id]);
                    continue;
                }

                // Lock room to prevent race conditions
                $lockedRoom = Room::lockForUpdate()->with('house')->find($room->id);

                if (!$lockedRoom) {
                    $this->warn("  ⚠ Không tìm thấy phòng cho booking #{$booking->id}. Bỏ qua.");
                    $this->newLine();
                    continue;
                }

                // Check if room is available (no active tenant)
                // rental_start_date and rental_end_date are already Carbon instances (cast in model)
                $hasActiveTenant = $lockedRoom->tenant_id
                    && $lockedRoom->rental_start_date
                    && $lockedRoom->rental_end_date
                    && $lockedRoom->rental_start_date->lte($today)
                    && $lockedRoom->rental_end_date->gte($today);

                if (!$hasActiveTenant) {
                    // Assign tenant to room
                    $user = $booking->user;
                    $tenantName = $booking->tenant_name ?? ($user ? $user->name : 'N/A');
                    $houseName = $lockedRoom->house->name ?? 'N/A';
                    $bookingCode = $booking->booking_code ?? "Booking #{$booking->id}";

                    // Display step-by-step info
                    $this->line("  → Phòng <fg=cyan>{$houseName} - Phòng {$lockedRoom->room_number} (Tầng {$lockedRoom->floor})</>");
                    $this->line("    Mã đặt phòng: <fg=yellow>{$bookingCode}</>");
                    $this->line("    Khách: <fg=yellow>{$tenantName}</>");
                    $this->line("    Ngày vào: <fg=green>{$booking->start_date->format('Y-m-d')}</>");
                    $this->line("    Ngày ra: <fg=green>{$booking->end_date->format('Y-m-d')}</>");
                    $this->line("    <fg=green>✓ Đã thêm khách vào phòng</>");
                    $this->newLine();

                    $lockedRoom->update([
                        'status' => 'occupied',
                        'tenant_id' => $booking->user_id,
                        'tenant_name' => $tenantName,
                        'rental_start_date' => $booking->start_date,
                        'rental_end_date' => $booking->end_date,
                    ]);

                    $newCheckIns++;
                    $processedRooms++;

                    // Collect info for display
                    $checkInRoomsList[] = [
                        'house_name' => $houseName,
                        'room_number' => $lockedRoom->room_number,
                        'floor' => $lockedRoom->floor,
                        'tenant_name' => $tenantName,
                        'booking_code' => $bookingCode,
                        'check_in_date' => $booking->start_date->format('Y-m-d'),
                        'check_out_date' => $booking->end_date->format('Y-m-d'),
                    ];

                    Log::info('Assigned new tenant to room for check-in', [
                        'booking_id' => $booking->id,
                        'room_id' => $lockedRoom->id,
                        'house_id' => $lockedRoom->house_id,
                        'room_number' => $lockedRoom->room_number,
                        'user_id' => $booking->user_id,
                        'check_in_date' => $booking->start_date->format('Y-m-d'),
                        'check_out_date' => $booking->end_date->format('Y-m-d'),
                    ]);
                } else {
                    $this->warn("  ⚠ Phòng {$lockedRoom->room_number} đã có khách đang ở. Không thể chuyển booking #{$booking->id} vào.");
                    $this->newLine();
                    Log::warning('Room already has active tenant, cannot assign new booking', [
                        'booking_id' => $booking->id,
                        'room_id' => $lockedRoom->id,
                        'existing_tenant_id' => $lockedRoom->tenant_id,
                    ]);
                }
            }

            // Step 3: Update booking_status for all paid bookings
            $this->info("=== Bước 3: Cập nhật trạng thái đặt phòng ===");
            $todayString = $today->toDateString();
            
            // Update upcoming bookings (start_date > today)
            $upcomingCount = Booking::where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('start_date', '>', $todayString)
                ->update(['booking_status' => 'upcoming']);
            
            // Update active bookings (start_date <= today <= end_date)
            $activeCount = Booking::where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('start_date', '<=', $todayString)
                ->where('end_date', '>=', $todayString)
                ->update(['booking_status' => 'active']);
            
            // Update past bookings (end_date < today)
            $pastCount = Booking::where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('end_date', '<', $todayString)
                ->update(['booking_status' => 'past']);
            
            if ($upcomingCount > 0 || $activeCount > 0 || $pastCount > 0) {
                $this->info("Đã cập nhật trạng thái cho {$upcomingCount} booking sắp tới, {$activeCount} booking đang ở, {$pastCount} booking đã ở.");
                $this->newLine();
            } else {
                $this->info("Không có booking nào cần cập nhật trạng thái.");
                $this->newLine();
            }

            DB::commit();

            // Display summary table
            $this->info("=== Tổng kết ===");
            $this->newLine();
            
            $summaryData = [
                ['Tổng số phòng đã xử lý', $processedRooms],
                ['Phòng hết hạn (đã xóa khách)', $expiredTenants],
                ['Phòng có khách chuyển vào', $newCheckIns],
            ];
            
            $this->table(
                ['Loại', 'Số lượng'],
                $summaryData
            );
            $this->newLine();

            // Display detailed tables
            if (count($expiredRoomsList) > 0) {
                $this->info("=== Chi tiết phòng hết hạn ({$expiredTenants} phòng) ===");
                $this->table(
                    ['Nhà trọ', 'Số phòng', 'Tầng', 'Người thuê', 'Ngày hết hạn'],
                    array_map(function ($room) {
                        return [
                            $room['house_name'],
                            $room['room_number'],
                            $room['floor'],
                            $room['tenant_name'],
                            $room['expired_date'],
                        ];
                    }, $expiredRoomsList)
                );
                $this->newLine();
            }

            if (count($checkInRoomsList) > 0) {
                $this->info("=== Chi tiết phòng có người chuyển vào ({$newCheckIns} phòng) ===");
                $this->table(
                    ['Nhà trọ', 'Số phòng', 'Tầng', 'Người thuê', 'Mã đặt phòng', 'Ngày vào', 'Ngày ra'],
                    array_map(function ($room) {
                        return [
                            $room['house_name'],
                            $room['room_number'],
                            $room['floor'],
                            $room['tenant_name'],
                            $room['booking_code'],
                            $room['check_in_date'],
                            $room['check_out_date'],
                        ];
                    }, $checkInRoomsList)
                );
                $this->newLine();
            }

            $this->info("<fg=green>✓ Cập nhật trạng thái phòng hoàn tất!</>");
            $this->newLine();

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating room status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("Error updating room status: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

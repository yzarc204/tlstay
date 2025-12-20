<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
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
        $today = Carbon::today();
        $processedRooms = 0;
        $expiredTenants = 0;
        $newCheckIns = 0;

        try {
            DB::beginTransaction();

            // Step 1: Remove expired tenants (rental_end_date < today)
            $expiredRooms = Room::where('status', 'occupied')
                ->whereNotNull('rental_end_date')
                ->where('rental_end_date', '<', $today)
                ->get();

            foreach ($expiredRooms as $room) {
                $expiredDate = $room->rental_end_date?->format('Y-m-d');
                $tenantId = $room->tenant_id;
                
                $room->update([
                    'status' => 'available',
                    'tenant_id' => null,
                    'tenant_name' => null,
                    'rental_start_date' => null,
                    'rental_end_date' => null,
                ]);
                $expiredTenants++;
                $processedRooms++;

                Log::info('Removed expired tenant from room', [
                    'room_id' => $room->id,
                    'house_id' => $room->house_id,
                    'room_number' => $room->room_number,
                    'tenant_id' => $tenantId,
                    'expired_date' => $expiredDate,
                ]);
            }

            // Step 2: Assign new tenants for check-in date (start_date = today and payment_status = 'paid')
            $checkInBookings = Booking::where('payment_status', 'paid')
                ->where('status', 'active')
                ->whereDate('start_date', $today)
                ->with(['room', 'user'])
                ->get();

            foreach ($checkInBookings as $booking) {
                $room = $booking->room;
                
                if (!$room) {
                    Log::warning('Booking has no room', ['booking_id' => $booking->id]);
                    continue;
                }

                // Lock room to prevent race conditions
                $lockedRoom = Room::lockForUpdate()->find($room->id);
                
                if (!$lockedRoom) {
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
                    $lockedRoom->update([
                        'status' => 'occupied',
                        'tenant_id' => $booking->user_id,
                        'tenant_name' => $booking->tenant_name ?? ($user ? $user->name : 'N/A'),
                        'rental_start_date' => $booking->start_date,
                        'rental_end_date' => $booking->end_date,
                    ]);

                    $newCheckIns++;
                    $processedRooms++;

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
                    Log::warning('Room already has active tenant, cannot assign new booking', [
                        'booking_id' => $booking->id,
                        'room_id' => $lockedRoom->id,
                        'existing_tenant_id' => $lockedRoom->tenant_id,
                    ]);
                }
            }

            DB::commit();

            $this->info("Room status update completed:");
            $this->info("- Expired tenants removed: {$expiredTenants}");
            $this->info("- New check-ins assigned: {$newCheckIns}");
            $this->info("- Total rooms processed: {$processedRooms}");

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

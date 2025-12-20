<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class GodSystemController extends Controller
{
    /**
     * Display God System page
     */
    public function index()
    {
        $systemTimeInfo = SystemTimeService::getTimeInfo();

        return Inertia::render('Admin/GodSystem/Index', [
            'systemTimeInfo' => $systemTimeInfo,
        ]);
    }

    /**
     * Update system time (God System)
     */
    public function updateSystemTime(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|string',
            'time' => 'nullable|string',
            'enabled' => 'required|boolean',
        ]);

        try {
            if ($validated['enabled']) {
                $dateInput = $validated['date'];
                
                // Parse date - support both DD/MM/YYYY and YYYY-MM-DD formats
                $date = null;
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateInput, $matches)) {
                    // DD/MM/YYYY format
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = $matches[3];
                    
                    // Validate date
                    if (!checkdate((int)$month, (int)$day, (int)$year)) {
                        return back()->with('error', 'Ngày không hợp lệ. Vui lòng kiểm tra lại.');
                    }
                    
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)) {
                    // YYYY-MM-DD format (already correct)
                    $date = $dateInput;
                } else {
                    return back()->with('error', 'Định dạng ngày không hợp lệ. Vui lòng sử dụng định dạng DD/MM/YYYY (ví dụ: 01/03/2025)');
                }
                
                $time = $validated['time'] ?? '00:00:00';
                
                // Validate time format manually (HH:MM or HH:MM:SS)
                if ($time && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9](:([0-5][0-9]))?$/', $time)) {
                    return back()->with('error', 'Định dạng giờ không hợp lệ. Vui lòng sử dụng định dạng HH:MM hoặc HH:MM:SS');
                }
                
                // Ensure time format is correct (add seconds if missing)
                if ($time && strlen($time) === 5) {
                    $time .= ':00';
                }
                
                if (!$time) {
                    $time = '00:00:00';
                }
                
                SystemTimeService::enableManualTime($date, $time);
                
                // Format date for display message
                $displayDate = date('d/m/Y', strtotime($date));
                $message = 'Đã bật chế độ thời gian thủ công. Hệ thống sẽ sử dụng: ' . $displayDate . ' ' . $time;
            } else {
                SystemTimeService::disableManualTime();
                $message = 'Đã tắt chế độ thời gian thủ công. Hệ thống sẽ sử dụng thời gian thực.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error updating system time', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Reset system time to real time
     */
    public function resetSystemTime()
    {
        try {
            SystemTimeService::disableManualTime();
            return back()->with('success', 'Đã reset về thời gian thực.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Trigger update room status manually
     */
    public function triggerUpdateRoom()
    {
        try {
            $exitCode = Artisan::call('rooms:update-status');
            $output = trim(Artisan::output());

            if ($exitCode === 0) {
                $message = 'Đã cập nhật trạng thái phòng thành công!';
                if ($output) {
                    $message .= "\n\n" . $output;
                }
                return back()->with('success', $message);
            } else {
                return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái phòng.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Get system time info
     */
    public function getSystemTimeInfo()
    {
        return response()->json(SystemTimeService::getTimeInfo());
    }
}

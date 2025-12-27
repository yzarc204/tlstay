<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Room;
use App\Models\Booking;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(): Response
    {
        $stats = [
            'total_houses' => House::count(),
            'total_rooms' => Room::count(),
            'occupied_rooms' => Room::where('status', 'active')->count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_users' => User::count(),
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'bookings_this_month' => Booking::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'revenue_this_month' => Booking::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->where('payment_status', 'paid')
                ->sum('total_price'),
        ];

        // Dữ liệu cho biểu đồ doanh thu 6 tháng gần nhất
        $revenueData = [];
        $revenueLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthRevenue = Booking::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('payment_status', 'paid')
                ->sum('total_price');
            $revenueData[] = $monthRevenue;
            $revenueLabels[] = $date->format('M Y');
        }

        // Dữ liệu cho biểu đồ đặt phòng 6 tháng gần nhất
        $bookingsData = [];
        $bookingsLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthBookings = Booking::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $bookingsData[] = $monthBookings;
            $bookingsLabels[] = $date->format('M Y');
        }

        // Dữ liệu cho biểu đồ trạng thái phòng
        $roomStatusData = [
            'available' => Room::where('status', 'available')->count(),
            'active' => Room::where('status', 'active')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'chartData' => [
                'revenue' => [
                    'labels' => $revenueLabels,
                    'data' => $revenueData,
                ],
                'bookings' => [
                    'labels' => $bookingsLabels,
                    'data' => $bookingsData,
                ],
                'roomStatus' => [
                    'available' => $roomStatusData['available'],
                    'occupied' => $roomStatusData['active'],
                ],
            ],
        ]);
    }
}

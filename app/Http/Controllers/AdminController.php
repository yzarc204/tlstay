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
            'occupied_rooms' => Room::where('status', 'occupied')->count(),
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

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the user's invoices.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $query = Invoice::where('user_id', $user->id)
            ->with(['booking.room', 'booking.house'])
            ->orderBy('start_date', 'desc')
            ->orderBy('end_date', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $invoices = $query->get()->map(function ($invoice) {
            $monthYear = '';
            if ($invoice->start_date && $invoice->end_date) {
                $monthYear = $invoice->start_date->format('d/m/Y') . ' - ' . $invoice->end_date->format('d/m/Y');
            } elseif ($invoice->month && $invoice->year) {
                $monthYear = "{$invoice->month}/{$invoice->year}";
            }

            $total = (float) (
                ($invoice->amount ?? 0) +
                ($invoice->electricity_amount ?? 0) +
                ($invoice->water_amount ?? 0) +
                ($invoice->other_fees ?? 0)
            );

            return [
                'id' => $invoice->id,
                'booking_id' => $invoice->booking_id,
                'month' => $invoice->month,
                'year' => $invoice->year,
                'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                'month_year' => $monthYear,
                'room_rent' => (float) ($invoice->amount ?? 0),
                'electricity_amount' => (float) ($invoice->electricity_amount ?? 0),
                'water_amount' => (float) ($invoice->water_amount ?? 0),
                'other_fees' => (float) ($invoice->other_fees ?? 0),
                'total' => $total,
                'status' => $invoice->status,
                'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                'notes' => $invoice->notes,
                'house_name' => $invoice->booking?->house?->name ?? 'N/A',
                'room_number' => $invoice->booking?->room?->room_number ?? 'N/A',
            ];
        });

        // Statistics
        $stats = [
            'total' => $invoices->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            'pending' => $invoices->where('status', 'pending')->count(),
            'total_amount' => $invoices->sum('total'),
            'paid_amount' => $invoices->where('status', 'paid')->sum('total'),
            'pending_amount' => $invoices->where('status', 'pending')->sum('total'),
        ];

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status ?? 'all',
            ],
        ]);
    }

    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $invoice = Invoice::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['booking.room', 'booking.house', 'user'])
            ->firstOrFail();

        $monthYear = '';
        if ($invoice->start_date && $invoice->end_date) {
            $monthYear = $invoice->start_date->format('d/m/Y') . ' - ' . $invoice->end_date->format('d/m/Y');
        } elseif ($invoice->month && $invoice->year) {
            $monthYear = "{$invoice->month}/{$invoice->year}";
        }

        $total = (float) (
            ($invoice->amount ?? 0) +
            ($invoice->electricity_amount ?? 0) +
            ($invoice->water_amount ?? 0) +
            ($invoice->other_fees ?? 0)
        );

        $invoiceData = [
            'id' => $invoice->id,
            'booking_id' => $invoice->booking_id,
            'month' => $invoice->month,
            'year' => $invoice->year,
            'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
            'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
            'month_year' => $monthYear,
            'room_rent' => (float) ($invoice->amount ?? 0),
            'electricity_amount' => (float) ($invoice->electricity_amount ?? 0),
            'water_amount' => (float) ($invoice->water_amount ?? 0),
            'other_fees' => (float) ($invoice->other_fees ?? 0),
            'total' => $total,
            'status' => $invoice->status,
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
            'notes' => $invoice->notes,
            'house_name' => $invoice->booking?->house?->name ?? 'N/A',
            'room_number' => $invoice->booking?->room?->room_number ?? 'N/A',
            'house_address' => $invoice->booking?->house?->address ?? 'N/A',
            'user_name' => $invoice->user->name ?? 'N/A',
            'user_email' => $invoice->user->email ?? 'N/A',
            'user_phone' => $invoice->user->phone ?? 'N/A',
            'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
        ];

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoiceData,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BankController extends Controller
{
    /**
     * Get list of banks from VietQR API
     */
    public function index()
    {
        // Cache for 24 hours
        $banks = Cache::remember('vietqr_banks', 60 * 60 * 24, function () {
            try {
                $response = Http::timeout(10)->get('https://api.vietqr.io/v2/banks');
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']) && is_array($data['data'])) {
                        return $data['data'];
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to fetch banks from VietQR API: ' . $e->getMessage());
            }
            
            // Return empty array if API fails
            return [];
        });

        return response()->json([
            'banks' => $banks,
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Get wallet balance if user is authenticated
        $walletBalance = 0;
        if ($user) {
            $wallet = \App\Models\Wallet::where('user_id', $user->id)->first();
            $walletBalance = $wallet ? (float) $wallet->balance : 0;
        }

        // Get site settings
        $siteSettings = \App\Models\Setting::getAll();

        // Get active social links
        $socialLinks = \App\Models\SocialLink::getActive();
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'id_card_number' => $user->id_card_number,
                    'id_card_issue_date' => $user->id_card_issue_date?->format('Y-m-d'),
                    'id_card_issue_place' => $user->id_card_issue_place,
                    'id_card_image' => $user->id_card_image ? \Illuminate\Support\Facades\Storage::url($user->id_card_image) : null,
                    'permanent_address' => $user->permanent_address,
                    'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                    'gender' => $user->gender,
                    'signature' => $user->signature,
                ] : null,
            ],
            'wallet' => [
                'balance' => $walletBalance,
            ],
            'siteSettings' => $siteSettings,
            'socialLinks' => $socialLinks->map(function ($link) {
                return [
                    'id' => $link->id,
                    'name' => $link->name,
                    'icon' => $link->icon,
                    'url' => $link->url,
                ];
            }),
        ];
    }
}

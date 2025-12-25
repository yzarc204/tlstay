<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $setting->value,
                        'type' => $setting->type,
                        'label' => $setting->label,
                        'description' => $setting->description,
                    ];
                })->values();
            });

        $socialLinks = SocialLink::orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'name' => $link->name,
                    'icon' => $link->icon,
                    'url' => $link->url,
                    'order' => $link->order,
                    'is_active' => $link->is_active,
                ];
            });

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'socialLinks' => $socialLinks,
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logoSetting = Setting::where('key', 'site_logo')->first();
                
                // Upload new logo and replace old one
                $logoUrl = $this->fileUploadService->replaceFile(
                    $request->file('logo'),
                    $logoSetting?->value,
                    'logos'
                );
                
                Setting::where('key', 'site_logo')
                    ->update(['value' => $logoUrl]);
            }
            
            // Handle delete logo
            if ($request->has('delete_logo') && ($request->input('delete_logo') === '1' || $request->input('delete_logo') === true || $request->input('delete_logo') === 'true')) {
                $logoSetting = Setting::where('key', 'site_logo')->first();
                
                if ($logoSetting && $logoSetting->value) {
                    $this->fileUploadService->deleteFile($logoSetting->value);
                }
                
                Setting::where('key', 'site_logo')
                    ->update(['value' => null]);
            }

            // Update other settings
            foreach ($validated['settings'] as $settingData) {
                // Skip logo setting as it's handled separately
                if ($settingData['key'] === 'site_logo') {
                    continue;
                }
                
                Setting::where('key', $settingData['key'])
                    ->update(['value' => $settingData['value'] ?? '']);
            }

            DB::commit();

            return back()->with('success', 'Cài đặt đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật cài đặt: ' . $e->getMessage());
        }
    }

    /**
     * Store social link
     */
    public function storeSocialLink(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $socialLink = SocialLink::create([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'url' => $validated['url'],
            'order' => $validated['order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Đã thêm link mạng xã hội thành công!');
    }

    /**
     * Update social link
     */
    public function updateSocialLink(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $socialLink = SocialLink::findOrFail($id);
        $socialLink->update($validated);

        return back()->with('success', 'Đã cập nhật link mạng xã hội thành công!');
    }

    /**
     * Delete social link
     */
    public function destroySocialLink($id)
    {
        $socialLink = SocialLink::findOrFail($id);
        $socialLink->delete();

        return back()->with('success', 'Đã xóa link mạng xã hội thành công!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of banners.
     */
    public function index(Request $request): Response
    {
        $query = Banner::query()->orderBy('order')->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $banners = $query->paginate(12)->withQueryString();

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create');
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'show_text' => 'nullable|boolean',
            'text_title' => 'nullable|string|max:255',
            'text_subtitle' => 'nullable|string|max:500',
            'text_line1' => 'nullable|string|max:500',
            'text_line2' => 'nullable|string|max:500',
            'text_line3' => 'nullable|string|max:500',
            'text_position' => 'nullable|in:left,center,right',
        ]);

        // Upload image
        if ($request->hasFile('image')) {
            $validated['image'] = $this->fileUploadService->uploadFile($request->file('image'), 'banners');
        }

        $validated['order'] = $validated['order'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['show_text'] = $validated['show_text'] ?? false;
        $validated['text_position'] = $validated['text_position'] ?? 'center';

        Banner::create($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã thêm banner thành công.');
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update the specified banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'show_text' => 'nullable|boolean',
            'text_title' => 'nullable|string|max:255',
            'text_subtitle' => 'nullable|string|max:500',
            'text_line1' => 'nullable|string|max:500',
            'text_line2' => 'nullable|string|max:500',
            'text_line3' => 'nullable|string|max:500',
            'text_position' => 'nullable|in:left,center,right',
        ]);

        // Upload new image if provided
        if ($request->hasFile('image')) {
            $validated['image'] = $this->fileUploadService->replaceFile(
                $request->file('image'),
                $banner->image,
                'banners'
            );
        } else {
            // Keep existing image - don't update the image field
            unset($validated['image']);
        }

        $banner->update($validated);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã cập nhật banner thành công.');
    }

    /**
     * Remove the specified banner.
     */
    public function destroy(Banner $banner)
    {
        // Delete image file
        $this->fileUploadService->deleteFile($banner->image);

        $banner->delete();

        return back()->with('success', 'Đã xóa banner thành công.');
    }
}

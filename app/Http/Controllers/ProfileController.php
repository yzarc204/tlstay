<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Display profile page
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        return Inertia::render('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'id_card_number' => $user->id_card_number,
                'id_card_issue_date' => $user->id_card_issue_date?->format('Y-m-d'),
                'id_card_issue_place' => $user->id_card_issue_place,
                'id_card_image' => $user->id_card_image ? Storage::url($user->id_card_image) : null,
                'permanent_address' => $user->permanent_address,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'signature' => $user->signature,
            ],
        ]);
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_card_number' => 'required|string|max:20',
            'id_card_issue_date' => 'required|date|before_or_equal:today',
            'id_card_issue_place' => 'required|string|max:255',
            'permanent_address' => 'required|string|max:500',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'signature' => 'nullable|string',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự',
            'id_card_number.required' => 'Vui lòng nhập số căn cước công dân',
            'id_card_issue_date.required' => 'Vui lòng chọn ngày cấp CCCD',
            'id_card_issue_date.before_or_equal' => 'Ngày cấp CCCD phải trước hoặc bằng ngày hiện tại',
            'id_card_issue_place.required' => 'Vui lòng nhập nơi cấp CCCD',
            'id_card_issue_place.max' => 'Nơi cấp CCCD không được vượt quá 255 ký tự',
            'permanent_address.required' => 'Vui lòng nhập địa chỉ thường trú',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại',
            'gender.required' => 'Vui lòng chọn giới tính',
            'gender.in' => 'Giới tính không hợp lệ',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'id_card_number' => $request->id_card_number,
            'id_card_issue_date' => $request->id_card_issue_date,
            'id_card_issue_place' => $request->id_card_issue_place,
            'permanent_address' => $request->permanent_address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'signature' => $request->signature,
        ]);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function updatePersonalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'id_card_number' => 'required|string|max:20',
            'id_card_issue_date' => 'required|date|before_or_equal:today',
            'id_card_issue_place' => 'required|string|max:255',
            'permanent_address' => 'required|string|max:500',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'id_card_image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // Max 5MB
        ], [
            'name.required' => 'Vui lòng nhập họ và tên',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự',
            'id_card_number.required' => 'Vui lòng nhập số căn cước công dân',
            'id_card_issue_date.required' => 'Vui lòng chọn ngày cấp CCCD',
            'id_card_issue_date.before_or_equal' => 'Ngày cấp CCCD phải trước hoặc bằng ngày hiện tại',
            'id_card_issue_place.required' => 'Vui lòng nhập nơi cấp CCCD',
            'id_card_issue_place.max' => 'Nơi cấp CCCD không được vượt quá 255 ký tự',
            'permanent_address.required' => 'Vui lòng nhập địa chỉ thường trú',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại',
            'gender.required' => 'Vui lòng chọn giới tính',
            'gender.in' => 'Giới tính không hợp lệ',
            'id_card_image.image' => 'File phải là ảnh',
            'id_card_image.mimes' => 'Ảnh phải có định dạng jpeg, jpg hoặc png',
            'id_card_image.max' => 'Kích thước ảnh không được vượt quá 5MB',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập');
        }

        $updateData = [
            'name' => $request->name,
            'id_card_number' => $request->id_card_number,
            'id_card_issue_date' => $request->id_card_issue_date,
            'id_card_issue_place' => $request->id_card_issue_place,
            'permanent_address' => $request->permanent_address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ];

        // Xử lý upload ảnh CCCD
        if ($request->hasFile('id_card_image')) {
            // Xóa ảnh cũ nếu có
            if ($user->id_card_image && Storage::disk('public')->exists($user->id_card_image)) {
                Storage::disk('public')->delete($user->id_card_image);
            }

            // Lưu ảnh mới
            $image = $request->file('id_card_image');
            $imageName = 'id_cards/' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public', $imageName);
            // Lưu đường dẫn tương đối để có thể lấy URL sau
            $updateData['id_card_image'] = $imageName;
        }

        $user->update($updateData);

        return back()->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function show(): Response
    {
        return Inertia::render('Register');
    }

    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        // Tạo user mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Mặc định là khách hàng
            'avatar' => $this->generateAvatarUrl($request->name),
        ]);

        // Fire event đăng ký (có thể dùng để gửi email xác thực)
        event(new Registered($user));

        // Tự động đăng nhập sau khi đăng ký
        Auth::login($user);

        // Redirect về trang chủ với thông báo thành công
        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công!');
    }

    /**
     * Generate avatar URL từ tên người dùng.
     */
    private function generateAvatarUrl(string $name): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=000066&color=fff&size=128';
    }
}

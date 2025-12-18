<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $query = User::query()
            ->withCount(['bookings', 'invoices'])
            ->orderBy('created_at', 'desc');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status (banned/active)
        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->banned();
            } elseif ($request->status === 'active') {
                $query->active();
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => [
                'required',
                'string',
                'regex:/^(0|\+84)[1-9][0-9]{8,9}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
            ],
            'role' => ['required', 'in:customer,manager'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'name.min' => 'Họ tên phải có ít nhất 2 ký tự',
            'email.required' => 'Vui lòng nhập địa chỉ email',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.unique' => 'Email này đã được sử dụng',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'role.required' => 'Vui lòng chọn vai trò',
            'role.in' => 'Vai trò không hợp lệ',
        ]);

        // Normalize email and phone
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['phone'] = preg_replace('/\s+/', '', $validated['phone']);

        $user = User::create($validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Đã tạo tài khoản thành công.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        $user->load([
            'bookings' => function ($query) {
                $query->latest()->limit(10);
            },
            'invoices' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'banned_at' => $user->banned_at?->toISOString(),
                'ban_reason' => $user->ban_reason,
                'created_at' => $user->created_at?->toISOString(),
                'id_card_number' => $user->id_card_number,
                'permanent_address' => $user->permanent_address,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'bookings' => $user->bookings,
                'invoices' => $user->invoices,
            ],
        ]);
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, User $user)
    {
        $validated = $request->validate([
            'ban_reason' => 'required|string|max:1000',
        ]);

        // Cannot ban managers
        if ($user->isManager()) {
            return back()->withErrors(['error' => 'Không thể khóa tài khoản quản lý.']);
        }

        // Cannot ban yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Bạn không thể khóa chính tài khoản của mình.']);
        }

        $user->update([
            'banned_at' => now(),
            'ban_reason' => $validated['ban_reason'],
        ]);

        return back()->with('success', 'Đã khóa tài khoản thành công.');
    }

    /**
     * Unban a user.
     */
    public function unban(User $user)
    {
        $user->update([
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        return back()->with('success', 'Đã mở khóa tài khoản thành công.');
    }
}

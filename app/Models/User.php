<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'banned_at',
        'ban_reason',
        'id_card_number',
        'id_card_issue_date',
        'id_card_issue_place',
        'id_card_image',
        'permanent_address',
        'date_of_birth',
        'gender',
        'signature',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned_at' => 'datetime',
            'date_of_birth' => 'date',
            'id_card_issue_date' => 'date',
        ];
    }

    /**
     * Get the houses owned by the user.
     */
    public function ownedHouses(): HasMany
    {
        return $this->hasMany(House::class, 'owner_id');
    }

    /**
     * Get the bookings for the user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the invoices for the user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the wishlist items for the user.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Scope a query to only include managers.
     */
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    /**
     * Scope a query to only include customers.
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    /**
     * Check if user is banned.
     */
    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    /**
     * Scope a query to only include banned users.
     */
    public function scopeBanned($query)
    {
        return $query->whereNotNull('banned_at');
    }

    /**
     * Scope a query to only include active (not banned) users.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('banned_at');
    }

    /**
     * Check if user has completed personal information required for booking.
     */
    public function hasCompletePersonalInfo(): bool
    {
        return !empty($this->id_card_number)
            && !empty($this->id_card_issue_date)
            && !empty($this->id_card_issue_place)
            && !empty($this->permanent_address)
            && !empty($this->date_of_birth)
            && !empty($this->gender);
    }
}

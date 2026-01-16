<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function orders()
    {
        return $this->hasMany(Order::class); // Orders placed by user (if customer? or meant staff? Requirement says 'assign order to a staff member')
        // Usually User is Customer. But here 'users' table has 'role'. Assuming User can be Customer too? 
        // User requirements: "roles: admin, staff".
        // Ah, maybe there is no 'customer' role? Just users who place orders?
        // "an order can contin multipal products... assign order to a staff member"
        // "view all orders only admin... view assiegned orders admin and staff both"
        // It implies Users are customers too, or maybe just generic Users.
        // I'll assume anyone can place an order? Or maybe Admin creates orders for customers?
        // "create order... assign order to a staff member".
        // I'll assume User is the customer.
    }

    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'assigned_staff_id');
    }
}

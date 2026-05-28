<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'profile_image',
        'role',
        'permissions',
        'status',
        'is_active',
        'is_protected',
        'two_factor_enabled',
        'email_verified_at',
        'email_verify_token',
        'login_attempts',
        'locked_until',
        'last_login',
        'last_login_ip',
        'two_factor_channel',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verify_token',
        'two_factor_code',
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
            'locked_until' => 'datetime',
            'last_login' => 'datetime',
            'two_factor_expires' => 'datetime',
            'is_active' => 'boolean',
            'is_protected' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function productReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}

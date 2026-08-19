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
        'wallet_balance',
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
            'wallet_balance' => 'decimal:2',
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

    public function walletTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerWalletTransaction::class);
    }

    public function creditWallet(float $amount, string $source, ?int $orderId = null, ?string $description = null, string $status = 'completed', ?\DateTimeInterface $availableAt = null): CustomerWalletTransaction
    {
        $amount = round(max(0, $amount), 2);
        if ($status === 'completed') {
            $this->increment('wallet_balance', $amount);
        }

        return $this->walletTransactions()->create([
            'order_id' => $orderId,
            'type' => 'credit',
            'source' => $source,
            'amount' => $amount,
            'balance_after' => $this->fresh()->wallet_balance,
            'description' => $description ?: "Wallet credited via {$source}",
            'status' => $status,
            'available_at' => $availableAt,
        ]);
    }

    public function debitWallet(float $amount, string $source, ?int $orderId = null, ?string $description = null): ?CustomerWalletTransaction
    {
        $amount = round(max(0, $amount), 2);
        if ($amount <= 0 || (float)$this->wallet_balance < $amount) {
            return null;
        }

        $this->decrement('wallet_balance', $amount);

        return $this->walletTransactions()->create([
            'order_id' => $orderId,
            'type' => 'debit',
            'source' => $source,
            'amount' => $amount,
            'balance_after' => $this->fresh()->wallet_balance,
            'description' => $description ?: "Wallet debited via {$source}",
            'status' => 'completed',
        ]);
    }
}

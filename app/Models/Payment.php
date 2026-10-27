<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'customer_id',
        'amount',
        'method',
        'status',
        'gateway',
        'gateway_reference',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function isUnpaid(): bool
    {
        return $this->status === PaymentStatus::Unpaid;
    }

    public function isExpired(): bool
    {
        return $this->status === PaymentStatus::Expired
            || ($this->isUnpaid() && $this->expires_at && $this->expires_at->isPast());
    }

    public function getMethodLabelAttribute(): string
    {
        return payment_method_label($this->method);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'PAY-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}

<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'payment_id',
        'customer_id',
        'store_id',
        'status',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_method',
        'shipping_cost',
        'subtotal',
        'commission_amount',
        'total',
        'tracking_number',
        'note',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_cost' => 'integer',
            'subtotal' => 'integer',
            'commission_amount' => 'integer',
            'total' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isPaid(): bool
    {
        return $this->payment?->isPaid() ?? false;
    }

    public function getShippingLabelAttribute(): string
    {
        return shipping_method($this->shipping_method)['label'] ?? $this->shipping_method;
    }

    public function getStatusStepAttribute(): int
    {
        $steps = OrderStatus::progressSteps();
        $index = array_search($this->status, $steps, true);

        return $index === false ? 0 : $index + 1;
    }

    public function canBeCancelledBy(User $user): bool
    {
        if (! $this->status->isCancellable()) {
            return false;
        }

        return $user->isCustomer()
            ? $this->customer_id === $user->id
            : ($user->isPartner() && $this->store_id === $user->store?->id);
    }

    public static function generateNumber(): string
    {
        // Gunakan 3 bytes acak (6 karakter hex) → 16.7 juta kemungkinan per hari,
        // jauh lebih aman dari sekadar 4 digit (10.000).
        do {
            $number = 'LM-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}

<?php

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\StoreStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'model_name',
        'description',
        'price',
        'stock',
        'condition',
        'status',
        'verification_status',
        'rejection_note',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'condition' => ProductCondition::class,
            'status' => ProductStatus::class,
            'verification_status' => VerificationStatus::class,
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class);
    }

    public function compatibilities(): HasMany
    {
        return $this->hasMany(ProductCompatibility::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->where('verification_status', VerificationStatus::Approved->value)
            ->where('status', ProductStatus::Active->value)
            ->whereHas('store', function (Builder $storeQuery) {
                $storeQuery->where('status', StoreStatus::Verified->value)
                    ->whereHas('user', fn (Builder $userQuery) => $userQuery->where('is_active', true));
            });
    }

    public function scopeWithRating(Builder $query): Builder
    {
        return $query->withAvg(
            ['reviews as rating_avg' => fn (Builder $reviewQuery) => $reviewQuery->where('is_hidden', false)],
            'rating'
        );
    }

    public function isPubliclyVisible(): bool
    {
        $this->loadMissing('store.user');

        return $this->verification_status === VerificationStatus::Approved
            && $this->status === ProductStatus::Active
            && $this->store?->status === StoreStatus::Verified
            && $this->store?->user?->is_active;
    }

    public function getImageUrlAttribute(): string
    {
        return lensmart_product_image($this->primaryImage?->path ?? $this->images->first()?->path);
    }

    public function getRatingAttribute(): float
    {
        if (array_key_exists('rating_avg', $this->attributes)) {
            return round((float) $this->attributes['rating_avg'], 1);
        }

        return round((float) ($this->reviews_avg_rating ?? 0), 1);
    }

    public function getRatingCountAttribute(): int
    {
        return (int) ($this->reviews_count ?? $this->reviews()->where('is_hidden', false)->count());
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$counter);
        }

        return $slug;
    }

    public function syncStockStatus(): void
    {
        if ($this->stock <= 0) {
            $this->status = ProductStatus::OutOfStock;
        } elseif ($this->status === ProductStatus::OutOfStock) {
            $this->status = ProductStatus::Active;
        }
    }
}

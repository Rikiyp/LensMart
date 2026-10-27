<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'unit', 'category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_specs')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function displayName(): string
    {
        return $this->unit ? "{$this->name} ({$this->unit})" : $this->name;
    }
}

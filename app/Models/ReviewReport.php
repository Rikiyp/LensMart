<?php

namespace App\Models;

use App\Enums\ReviewReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReport extends Model
{
    protected $fillable = ['review_id', 'store_id', 'reason', 'status', 'admin_response'];

    protected function casts(): array
    {
        return [
            'status' => ReviewReportStatus::class,
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}

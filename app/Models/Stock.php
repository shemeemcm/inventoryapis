<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'quantity'   => 'integer',
    ];

    /**
     * The product this stock belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The warehouse this stock is stored in.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Check if this stock record is expiring within 7 days.
     */
    public function isExpiringSoon(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->lte(now()->addDays(7));
    }

    /**
     * Scope: only near-expiring records.
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now()->addDays(7));
    }

    /**
     * Scope: only non-expired records.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
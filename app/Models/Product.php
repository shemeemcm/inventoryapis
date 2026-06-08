<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'base_price',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    /**
     * A product can have stock in many warehouses.
     */
    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Total quantity across all warehouses.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->stock->sum('quantity');
    }

    /**
     * Stock records expiring within 7 days.
     */
    public function getExpiringSoonAttribute()
    {
        return $this->stock->filter(
            fn($s) => $s->expires_at && $s->expires_at->lte(now()->addDays(7))
        );
    }
}
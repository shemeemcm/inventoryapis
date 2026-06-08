<?php

namespace App\Services;

use App\Models\Product;

class DynamicPricingService
{
    /**
     * Calculate the dynamic price for a product
     * based on total stock quantity and expiry dates.
     */



    public function calculatePrice(Product $product): array
    {
        $totalQty    = $product->stock->sum('quantity');
        $expiringQty = $product->stock
            ->filter(fn($s) => $s->expires_at && $s->expires_at->lte(now()->addDays(7)))
            ->sum('quantity');

        $price = (float) $product->base_price;

        // ── Rule 1: Quantity-based adjustment ──────────────────────────
        if ($totalQty === 0) {
            // No stock at all — no adjustment
        } elseif ($totalQty < 10) {
            $price *= 1.30;       // +30% low stock
        } elseif ($totalQty <= 50) {
            $price *= 1.10;       // +10% medium stock
        } elseif ($totalQty > 100) {
            $price *= 0.80;       // -20% high stock
        }
        // between 51–100: no adjustment

        // ── Rule 2: Expiry discount ────────────────────────────────────
        if ($totalQty > 0 && $expiringQty > 0) {
            $expiryDiscount = ($expiringQty / $totalQty) * 0.25;
            $price *= (1 - $expiryDiscount);
        }

        return [
            'base_price'      => (float) $product->base_price,
            'dynamic_price'   => round($price, 2),
            'total_quantity'  => $totalQty,
            'expiring_qty'    => $expiringQty,
            'price_breakdown' => $this->breakdown($product->base_price, $totalQty, $expiringQty),
        ];
    }
    private function breakdown(float $basePrice, int $totalQty, int $expiringQty): array
    {
        $rules = [];

        if ($totalQty === 0) {
            $rules[] = 'No stock available: no adjustment';
        } elseif ($totalQty < 10) {
            $rules[] = 'Low stock (< 10 units): +30%';
        } elseif ($totalQty <= 50) {
            $rules[] = 'Medium stock (10–50 units): +10%';
        } elseif ($totalQty > 100) {
            $rules[] = 'High stock (> 100 units): -20%';
        } else {
            $rules[] = 'Normal stock (51–100 units): no adjustment';
        }

        if ($totalQty > 0 && $expiringQty > 0) {
            $pct     = round(($expiringQty / $totalQty) * 25, 1);
            $rules[] = "Expiring soon ({$expiringQty} of {$totalQty} units): -{$pct}%";
        }

        return $rules;
    }

    /**
     * Calculate prices for a collection of products at once.
     * Used in ProductController index to avoid N+1.
     */
    public function calculateForCollection(\Illuminate\Support\Collection $products): \Illuminate\Support\Collection
    {
        return $products->map(function (Product $product) {
            return array_merge(
                $product->toArray(),
                $this->calculatePrice($product)
            );
        });
    }
}

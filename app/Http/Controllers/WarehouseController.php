<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    /**
     * GET /api/warehouses/{id}/report
     * Show all products and stock in a warehouse.
     * Highlights items expiring within 7 days.
     */
    public function report(Warehouse $warehouse): JsonResponse
    {
        // Load stock with product details
        $stock = $warehouse->stock()->with('product')->get();

        // Separate normal and expiring items
        $normalItems   = [];
        $expiringItems = [];

        foreach ($stock as $record) {
            $item = [
                'stock_id'     => $record->id,
                'product_id'   => $record->product_id,
                'product_name' => $record->product->name,
                'base_price'   => $record->product->base_price,
                'quantity'     => $record->quantity,
                'expires_at'   => $record->expires_at,
                'expiring_soon'=> $record->isExpiringSoon(),
            ];

            if ($record->isExpiringSoon()) {
                $expiringItems[] = $item;
            } else {
                $normalItems[] = $item;
            }
        }

        // Summary totals
        $totalUnits    = $stock->sum('quantity');
        $expiringUnits = $stock->filter(fn($s) => $s->isExpiringSoon())->sum('quantity');

        return response()->json([
            'message'   => 'Warehouse report generated successfully.',
            'warehouse' => [
                'id'        => $warehouse->id,
                'name'      => $warehouse->name,
                'latitude'  => $warehouse->latitude,
                'longitude' => $warehouse->longitude,
            ],
            'summary' => [
                'total_products'  => $stock->count(),
                'total_units'     => $totalUnits,
                'expiring_units'  => $expiringUnits,
                'normal_units'    => $totalUnits - $expiringUnits,
            ],
            'expiring_soon' => $expiringItems,  // highlighted separately
            'stock'         => $normalItems,
        ], 200);
    }
}
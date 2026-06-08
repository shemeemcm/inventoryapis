<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRequest;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;

class StockController extends Controller
{
    /**
     * POST /api/stock
     * Create or update stock for a product in a warehouse.
     */
    public function store(StockRequest $request): JsonResponse
    {
        $stock = Stock::updateOrCreate(
            // Match on these two keys
            [
                'product_id'   => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
            ],
            // Update or set these values
            [
                'quantity'   => $request->quantity,
                'expires_at' => $request->expires_at,
            ]
        );

        $isNew   = $stock->wasRecentlyCreated;
        $message = $isNew ? 'Stock created successfully.' : 'Stock updated successfully.';

        return response()->json([
            'message' => $message,
            'data'    => [
                'id'           => $stock->id,
                'product_id'   => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'quantity'     => $stock->quantity,
                'expires_at'   => $stock->expires_at,
                'created_at'   => $stock->created_at,
                'updated_at'   => $stock->updated_at,
            ],
        ], $isNew ? 201 : 200);
    }
}
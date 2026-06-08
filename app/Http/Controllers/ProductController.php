<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\DynamicPricingService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private DynamicPricingService $pricing
    ) {}

    /**
     * GET /api/products
     * Return all products with dynamic prices applied.
     */
    public function index(): JsonResponse
    {
        // Eager load stock to avoid N+1 query problem
        $products = Product::with('stock')->get();

        $data = $products->map(function (Product $product) {
            $pricing = $this->pricing->calculatePrice($product);

            return [
                'id'              => $product->id,
                'name'            => $product->name,
                'base_price'      => $pricing['base_price'],
                'dynamic_price'   => $pricing['dynamic_price'],
                'total_quantity'  => $pricing['total_quantity'],
                'expiring_qty'    => $pricing['expiring_qty'],
                'price_breakdown' => $pricing['price_breakdown'],
                'created_at'      => $product->created_at,
                'updated_at'      => $product->updated_at,
            ];
        });

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'data'    => $data,
        ], 200);
    }
}
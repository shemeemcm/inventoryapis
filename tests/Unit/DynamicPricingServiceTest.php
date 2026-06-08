<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Services\DynamicPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DynamicPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicPricingService $service;
    private Warehouse $warehouse;
    private Warehouse $warehouse2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DynamicPricingService();

        // Create warehouses once — reused in all tests
        $this->warehouse  = Warehouse::create([
            'name'      => 'Test Warehouse 1',
            'latitude'  => 51.5074,
            'longitude' => -0.1278,
        ]);

        $this->warehouse2 = Warehouse::create([
            'name'      => 'Test Warehouse 2',
            'latitude'  => 53.4808,
            'longitude' => -2.2426,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // Helper — creates a product with stock quickly
    // ────────────────────────────────────────────────────────────────────
    private function makeProduct(float $basePrice, int $quantity, ?string $expiresAt = null): Product
    {
        $product = Product::create([
            'name'       => 'Test Product',
            'base_price' => $basePrice,
        ]);

        Stock::create([
            'product_id'   => $product->id,
            'warehouse_id' => $this->warehouse->id,  // ← use real warehouse
            'quantity'     => $quantity,
            'expires_at'   => $expiresAt,
        ]);

        return $product->load('stock');
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 1: Low stock (< 10 units) → +30%
    // ────────────────────────────────────────────────────────────────────
    public function test_low_stock_increases_price_by_30_percent(): void
    {
        $product = $this->makeProduct(basePrice: 100.00, quantity: 5);

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(130.00, $result['dynamic_price']);
        $this->assertEquals(5,      $result['total_quantity']);
        $this->assertEquals(0,      $result['expiring_qty']);
        $this->assertContains('Low stock (< 10 units): +30%', $result['price_breakdown']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 2: Medium stock (10–50 units) → +10%
    // ────────────────────────────────────────────────────────────────────
    public function test_medium_stock_increases_price_by_10_percent(): void
    {
        $product = $this->makeProduct(basePrice: 200.00, quantity: 30);

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(220.00, $result['dynamic_price']);
        $this->assertEquals(30,     $result['total_quantity']);
        $this->assertContains('Medium stock (10–50 units): +10%', $result['price_breakdown']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 3: High stock (> 100 units) → -20%
    // ────────────────────────────────────────────────────────────────────
    public function test_high_stock_decreases_price_by_20_percent(): void
    {
        $product = $this->makeProduct(basePrice: 150.00, quantity: 120);

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(120.00, $result['dynamic_price']);
        $this->assertEquals(120,    $result['total_quantity']);
        $this->assertContains('High stock (> 100 units): -20%', $result['price_breakdown']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 4: Normal stock (51–100 units) → no change
    // ────────────────────────────────────────────────────────────────────
    public function test_normal_stock_does_not_change_price(): void
    {
        $product = $this->makeProduct(basePrice: 100.00, quantity: 75);

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(100.00, $result['dynamic_price']);
        $this->assertContains('Normal stock (51–100 units): no adjustment', $result['price_breakdown']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 5: Stock expiring within 7 days → -25% proportional
    // ────────────────────────────────────────────────────────────────────
    public function test_expiring_stock_applies_discount(): void
    {
        $product = Product::create([
            'name'       => 'Expiring Product',
            'base_price' => 100.00,
        ]);

        // 20 units expiring in 3 days
        Stock::create([
            'product_id'   => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity'     => 20,
            'expires_at'   => now()->addDays(3),
        ]);

        // 10 units NOT expiring soon
        Stock::create([
            'product_id'   => $product->id,
            'warehouse_id' => $this->warehouse2->id,
            'quantity'     => 10,
            'expires_at'   => now()->addDays(30),
        ]);

        $product = $product->load('stock');
        $result  = $this->service->calculatePrice($product);

        // total = 30 → medium stock +10% → 110
        // expiring = 20/30 → discount = (20/30) * 0.25 = 0.1667
        // final = 110 * (1 - 0.1667) = 91.67
        $this->assertEquals(20,    $result['expiring_qty']);
        $this->assertEquals(30,    $result['total_quantity']);
        $this->assertEquals(91.67, $result['dynamic_price']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 6: Stock expiring AFTER 7 days → no expiry discount
    // ────────────────────────────────────────────────────────────────────
    public function test_stock_expiring_after_7_days_has_no_expiry_discount(): void
    {
        $product = $this->makeProduct(
            basePrice: 100.00,
            quantity:  5,
            expiresAt: now()->addDays(10)->toDateTimeString()
        );

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(0,      $result['expiring_qty']);
        $this->assertEquals(130.00, $result['dynamic_price']); // only +30% low stock
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 7: Zero stock → no adjustment, base price returned
    // ────────────────────────────────────────────────────────────────────
    public function test_zero_stock_returns_base_price(): void
    {
        // Create product with NO stock at all
        $product = Product::create([
            'name'       => 'Empty Product',
            'base_price' => 100.00,
        ]);

        // Load empty stock relation — do NOT call makeProduct
        $product->setRelation('stock', collect());

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(100.00, $result['dynamic_price']);
        $this->assertEquals(0,      $result['total_quantity']);
    }

    // ────────────────────────────────────────────────────────────────────
    // Test 8: base_price is never modified
    // ────────────────────────────────────────────────────────────────────
    public function test_base_price_is_always_returned_unchanged(): void
    {
        $product = $this->makeProduct(basePrice: 250.00, quantity: 5);

        $result = $this->service->calculatePrice($product);

        $this->assertEquals(250.00, $result['base_price']);
        $this->assertEquals(325.00, $result['dynamic_price']); // +30%
    }
    
}
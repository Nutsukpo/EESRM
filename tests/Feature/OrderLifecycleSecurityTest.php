<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderLifecycleSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeCashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    private function makeOrder(User $cashier, string $status = 'pending', string $paymentStatus = 'unpaid'): Order
    {
        return Order::create([
            'order_number' => 'LIFECYCLE-' . strtoupper($status) . '-' . strtoupper($paymentStatus),
            'cashier_id' => $cashier->id,
            'order_type' => 'takeaway',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
        ]);
    }

    private function makeMenuItem(): int
    {
        $categoryId = DB::table('menu_categories')->insertGetId([
            'name' => 'Lifecycle Test Category',
            'description' => 'Test category',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('menu_items')->insertGetId([
            'menu_category_id' => $categoryId,
            'name' => 'Lifecycle Test Meal',
            'description' => 'Test meal',
            'price' => 120,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function updatePayload(int $menuItemId): array
    {
        return [
            'order_type' => 'takeaway',
            'items_json' => json_encode([
                ['id' => $menuItemId, 'quantity' => 1],
            ]),
        ];
    }

    public function test_unpaid_pending_order_can_be_edited(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier);
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->put(route('orders.update', $order), $this->updatePayload($menuItemId))
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'unpaid',
            'total' => 120,
        ]);
    }

    public function test_paid_order_cannot_be_edited(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier, 'pending', 'paid');
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->from(route('orders.edit', $order))
            ->put(route('orders.update', $order), $this->updatePayload($menuItemId))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'total' => 100,
        ]);
    }

    public function test_served_order_cannot_be_edited(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier, 'served', 'unpaid');
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->from(route('orders.edit', $order))
            ->put(route('orders.update', $order), $this->updatePayload($menuItemId))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'served',
            'total' => 100,
        ]);
    }

    public function test_cancelled_order_cannot_be_edited(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier, 'cancelled', 'unpaid');
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->from(route('orders.edit', $order))
            ->put(route('orders.update', $order), $this->updatePayload($menuItemId))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'total' => 100,
        ]);
    }
}

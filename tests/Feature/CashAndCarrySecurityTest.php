<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CashAndCarrySecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'cashier'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makeOrder(User $cashier, string $number): Order
    {
        return Order::create([
            'order_number' => $number,
            'cashier_id' => $cashier->id,
            'order_type' => 'takeaway',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
        ]);
    }

    private function makeMenuItem(): int
    {
        $categoryId = DB::table('menu_categories')->insertGetId([
            'name' => 'Security Test Category',
            'description' => 'Test category',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('menu_items')->insertGetId([
            'menu_category_id' => $categoryId,
            'name' => 'Security Test Meal',
            'description' => 'Test meal',
            'price' => 100,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_pos_ignores_forged_cashier_id(): void
    {
        $cashier = $this->makeUser();
        $otherCashier = $this->makeUser();
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->post(route('pos.store'), [
                'order_type' => 'takeaway',
                'cashier_id' => $otherCashier->id,
                'items_json' => json_encode([
                    ['id' => $menuItemId, 'quantity' => 1],
                ]),
            ])
            ->assertRedirect(route('pos.index'));

        $this->assertDatabaseHas('orders', [
            'cashier_id' => $cashier->id,
            'payment_status' => 'unpaid',
            'total' => 100,
        ]);

        $this->assertDatabaseMissing('orders', [
            'cashier_id' => $otherCashier->id,
        ]);
    }

    public function test_payment_ignores_forged_received_by(): void
    {
        $cashier = $this->makeUser();
        $otherCashier = $this->makeUser();
        $order = $this->makeOrder($cashier, 'FORGED-RECEIVER-ORDER');

        $this->actingAs($cashier)
            ->post(route('payments.store', $order), [
                'method' => 'cash',
                'amount' => '100.00',
                'received_by' => $otherCashier->id,
                'payment_reference' => 'SECURITY-RECEIVER-TEST',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'received_by' => $cashier->id,
            'amount' => 100,
            'status' => 'completed',
        ]);

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
            'received_by' => $otherCashier->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'pending',
        ]);
    }

    public function test_partial_payment_is_rejected(): void
    {
        $cashier = $this->makeUser();
        $order = $this->makeOrder($cashier, 'PARTIAL-PAYMENT-ORDER');

        $this->actingAs($cashier)
            ->from(route('payments.create', $order))
            ->post(route('payments.store', $order), [
                'method' => 'cash',
                'amount' => '50.00',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseMissing('payments', [
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_duplicate_payment_is_rejected(): void
    {
        $cashier = $this->makeUser();
        $order = $this->makeOrder($cashier, 'DUPLICATE-PAYMENT-ORDER');

        Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => 100,
            'status' => 'completed',
            'payment_reference' => 'EXISTING-SECURITY-PAYMENT',
            'received_by' => $cashier->id,
            'paid_at' => now(),
            'metadata' => [],
        ]);

        $order->update(['payment_status' => 'paid']);

        $this->actingAs($cashier)
            ->from(route('payments.create', $order))
            ->post(route('payments.store', $order), [
                'method' => 'cash',
                'amount' => '100.00',
                'payment_reference' => 'DUPLICATE-ATTEMPT',
            ])
            ->assertSessionHasErrors('amount');

        $this->assertSame(
            1,
            Payment::where('order_id', $order->id)->count()
        );

        $this->assertDatabaseMissing('payments', [
            'payment_reference' => 'DUPLICATE-ATTEMPT',
        ]);
    }

    public function test_cashier_cannot_pay_for_another_cashiers_order(): void
    {
        $cashierA = $this->makeUser();
        $cashierB = $this->makeUser();

        $orderB = $this->makeOrder(
            $cashierB,
            'CROSS-CASHIER-PAYMENT-ORDER'
        );

        $this->actingAs($cashierA)
            ->post(route('payments.store', $orderB), [
                'method' => 'cash',
                'amount' => '100.00',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('payments', [
            'order_id' => $orderB->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $orderB->id,
            'payment_status' => 'unpaid',
        ]);
    }
}

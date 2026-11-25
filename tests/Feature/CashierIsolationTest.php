<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function createOrder(User $cashier, string $number): Order
    {
        return Order::create([
            'order_number' => $number,
            'cashier_id' => $cashier->id,
            'order_type' => 'takeaway',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100.00,
            'discount' => 0,
            'tax' => 0,
            'total' => 100.00,
        ]);
    }

    private function createPayment(
        Order $order,
        User $cashier,
        string $reference
    ): Payment {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'cash',
            'amount' => 100.00,
            'status' => 'completed',
            'payment_reference' => $reference,
            'received_by' => $cashier->id,
            'paid_at' => now(),
            'metadata' => [],
        ]);
    }

    public function test_cashier_only_sees_their_own_orders(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderA = $this->createOrder($cashierA, 'ORDER-CASHIER-A');
        $orderB = $this->createOrder($cashierB, 'ORDER-CASHIER-B');

        $this->actingAs($cashierA)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('ORDER-CASHIER-A')
            ->assertDontSee('ORDER-CASHIER-B');
    }

    public function test_cashier_cannot_open_another_cashiers_order(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderB = $this->createOrder($cashierB, 'ORDER-PRIVATE-B');

        $this->actingAs($cashierA)
            ->get(route('orders.show', $orderB))
            ->assertForbidden();
    }

    public function test_cashier_cannot_edit_another_cashiers_order(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderB = $this->createOrder($cashierB, 'ORDER-EDIT-B');

        $this->actingAs($cashierA)
            ->get(route('orders.edit', $orderB))
            ->assertForbidden();
    }

    public function test_cashier_cannot_update_another_cashiers_order(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderB = $this->createOrder($cashierB, 'ORDER-UPDATE-B');

        $this->actingAs($cashierA)
            ->put(route('orders.update', $orderB), [
                'order_type' => 'takeaway',
                'items_json' => json_encode([
                    ['id' => 1, 'quantity' => 1],
                ]),
            ])
            ->assertForbidden();
    }

    public function test_cashier_only_sees_payments_they_received(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderA = $this->createOrder($cashierA, 'PAYMENT-ORDER-A');
        $orderB = $this->createOrder($cashierB, 'PAYMENT-ORDER-B');

        $this->createPayment($orderA, $cashierA, 'PAYMENT-REF-A');
        $this->createPayment($orderB, $cashierB, 'PAYMENT-REF-B');

        $this->actingAs($cashierA)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('PAYMENT-REF-A')
            ->assertDontSee('PAYMENT-REF-B');
    }

    public function test_cashier_cannot_open_another_cashiers_payment_form(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderB = $this->createOrder($cashierB, 'PAYMENT-FORM-B');

        $this->actingAs($cashierA)
            ->get(route('payments.create', $orderB))
            ->assertForbidden();
    }

    public function test_cashier_cannot_open_another_cashiers_payment_receipt(): void
    {
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderB = $this->createOrder($cashierB, 'RECEIPT-ORDER-B');
        $paymentB = $this->createPayment(
            $orderB,
            $cashierB,
            'RECEIPT-REF-B'
        );

        $this->actingAs($cashierA)
            ->get(route('payments.receipt', $paymentB))
            ->assertForbidden();
    }

    public function test_owner_can_see_both_cashiers_orders_and_payments(): void
    {
        $owner = $this->createUser('owner');
        $cashierA = $this->createUser('cashier');
        $cashierB = $this->createUser('cashier');

        $orderA = $this->createOrder($cashierA, 'OWNER-ORDER-A');
        $orderB = $this->createOrder($cashierB, 'OWNER-ORDER-B');

        $this->createPayment($orderA, $cashierA, 'OWNER-PAYMENT-A');
        $this->createPayment($orderB, $cashierB, 'OWNER-PAYMENT-B');

        $this->actingAs($owner)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('OWNER-ORDER-A')
            ->assertSee('OWNER-ORDER-B');

        $this->actingAs($owner)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('OWNER-PAYMENT-A')
            ->assertSee('OWNER-PAYMENT-B');
    }
}

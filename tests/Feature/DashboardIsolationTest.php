<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function makeOrder(User $cashier, string $number): Order
    {
        return Order::create([
            'order_number' => $number,
            'cashier_id' => $cashier->id,
            'order_type' => 'takeaway',
            'status' => 'pending',
            'payment_status' => 'paid',
            'subtotal' => 100,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
        ]);
    }

    private function makePayment(
        Order $order,
        User $cashier,
        string $reference,
        string $method,
        float $amount
    ): Payment {
        return Payment::create([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $amount,
            'status' => 'completed',
            'payment_reference' => $reference,
            'received_by' => $cashier->id,
            'paid_at' => now(),
            'metadata' => [],
        ]);
    }

    public function test_cashier_dashboard_only_contains_their_own_figures_and_records(): void
    {
        $cashierA = $this->makeUser('cashier');
        $cashierB = $this->makeUser('cashier');

        $orderA = $this->makeOrder($cashierA, 'DASHBOARD-ORDER-A');
        $orderB = $this->makeOrder($cashierB, 'DASHBOARD-ORDER-B');

        $this->makePayment(
            $orderA,
            $cashierA,
            'DASHBOARD-PAYMENT-A',
            'cash',
            100
        );

        $this->makePayment(
            $orderB,
            $cashierB,
            'DASHBOARD-PAYMENT-B',
            'mobile_money',
            200
        );

        $response = $this->actingAs($cashierA)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('todaySales', 100.0);
        $response->assertViewHas('paymentsToday', 1);
        $response->assertViewHas('ordersToday', 1);
        $response->assertViewHas('cashSales', 100.0);
        $response->assertViewHas('mobileMoneySales', 0.0);
        $response->assertViewHas('cardSales', 0.0);

        $response->assertViewHas('recentOrders', function ($orders) use ($orderA, $orderB) {
            return $orders->contains('id', $orderA->id)
                && !$orders->contains('id', $orderB->id);
        });

        $response->assertViewHas('recentPayments', function ($payments) {
            return $payments->contains('payment_reference', 'DASHBOARD-PAYMENT-A')
                && !$payments->contains('payment_reference', 'DASHBOARD-PAYMENT-B');
        });

        $response->assertSee('DASHBOARD-ORDER-A');
        $response->assertDontSee('DASHBOARD-ORDER-B');
        $response->assertSee('DASHBOARD-PAYMENT-A');
        $response->assertDontSee('DASHBOARD-PAYMENT-B');
    }

    public function test_owner_dashboard_contains_combined_figures_and_records(): void
    {
        $owner = $this->makeUser('owner');
        $cashierA = $this->makeUser('cashier');
        $cashierB = $this->makeUser('cashier');

        $orderA = $this->makeOrder($cashierA, 'OWNER-DASHBOARD-ORDER-A');
        $orderB = $this->makeOrder($cashierB, 'OWNER-DASHBOARD-ORDER-B');

        $this->makePayment(
            $orderA,
            $cashierA,
            'OWNER-DASHBOARD-PAYMENT-A',
            'cash',
            100
        );

        $this->makePayment(
            $orderB,
            $cashierB,
            'OWNER-DASHBOARD-PAYMENT-B',
            'mobile_money',
            200
        );

        $response = $this->actingAs($owner)
            ->get(route('dashboard'));

        $response->assertOk();

        $response->assertViewHas('todaySales', 300.0);
        $response->assertViewHas('paymentsToday', 2);
        $response->assertViewHas('ordersToday', 2);
        $response->assertViewHas('cashSales', 100.0);
        $response->assertViewHas('mobileMoneySales', 200.0);

        $response->assertViewHas('recentOrders', function ($orders) use ($orderA, $orderB) {
            return $orders->contains('id', $orderA->id)
                && $orders->contains('id', $orderB->id);
        });

        $response->assertViewHas('recentPayments', function ($payments) {
            return $payments->contains('payment_reference', 'OWNER-DASHBOARD-PAYMENT-A')
                && $payments->contains('payment_reference', 'OWNER-DASHBOARD-PAYMENT-B');
        });

        $response->assertSee('OWNER-DASHBOARD-ORDER-A');
        $response->assertSee('OWNER-DASHBOARD-ORDER-B');
        $response->assertSee('OWNER-DASHBOARD-PAYMENT-A');
        $response->assertSee('OWNER-DASHBOARD-PAYMENT-B');
    }
}

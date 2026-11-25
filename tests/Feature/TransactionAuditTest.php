<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionAuditTest extends TestCase
{
    use RefreshDatabase;

    private function makeCashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    private function makeMenuItem(): int
    {
        $categoryId = DB::table('menu_categories')->insertGetId([
            'name' => 'Transaction Audit Category',
            'description' => 'Test category',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('menu_items')->insertGetId([
            'menu_category_id' => $categoryId,
            'name' => 'Transaction Audit Meal',
            'description' => 'Test meal',
            'price' => 120,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    public function test_order_update_records_correct_old_and_new_values(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier, 'AUDIT-UPDATE-ORDER');
        $menuItemId = $this->makeMenuItem();

        $this->actingAs($cashier)
            ->put(route('orders.update', $order), [
                'order_type' => 'dine_in',
                'items_json' => json_encode([
                    ['id' => $menuItemId, 'quantity' => 2],
                ]),
            ])
            ->assertRedirect(route('orders.show', $order));

        $audit = AuditLog::query()
            ->where('action', 'order_updated')
            ->where('auditable_type', $order->getMorphClass())
            ->where('auditable_id', $order->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($cashier->id, $audit->user_id);

        // Verify the state before the update.
        $this->assertSame('takeaway', $audit->old_values['order_type']);
        $this->assertEquals(100, $audit->old_values['total']);

        // Verify the state after the update.
        $this->assertSame('dine_in', $audit->new_values['order_type']);
        $this->assertEquals(240, $audit->new_values['total']);

        $this->assertCount(1, $audit->new_values['items']);
        $this->assertSame(
            'Transaction Audit Meal',
            $audit->new_values['items'][0]['name']
        );
        $this->assertSame(2, $audit->new_values['items'][0]['quantity']);
        $this->assertEquals(120, $audit->new_values['items'][0]['unit_price']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_type' => 'dine_in',
            'total' => 240,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_successful_payment_records_correct_audit_values(): void
    {
        $cashier = $this->makeCashier();
        $order = $this->makeOrder($cashier, 'AUDIT-PAYMENT-ORDER');

        $this->actingAs($cashier)
            ->post(route('payments.store', $order), [
                'method' => 'mobile_money',
                'amount' => '100.00',
                'payment_reference' => 'AUDIT-MOMO-REFERENCE',
            ])
            ->assertRedirect();

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->firstOrFail();

        $audit = AuditLog::query()
            ->where('action', 'sale_recorded')
            ->where('auditable_type', $payment->getMorphClass())
            ->where('auditable_id', $payment->id)
            ->firstOrFail();

        $this->assertSame($cashier->id, $audit->user_id);

        // Verify the state before payment.
        $this->assertSame('unpaid', $audit->old_values['payment_status']);
        $this->assertSame('pending', $audit->old_values['order_status']);

        // Verify the recorded sale.
        $this->assertSame($payment->id, $audit->new_values['payment_id']);
        $this->assertSame($order->id, $audit->new_values['order_id']);
        $this->assertSame(
            'AUDIT-PAYMENT-ORDER',
            $audit->new_values['order_number']
        );
        $this->assertSame('mobile_money', $audit->new_values['method']);
        $this->assertEquals(100, $audit->new_values['amount']);
        $this->assertSame(
            'AUDIT-MOMO-REFERENCE',
            $audit->new_values['payment_reference']
        );
        $this->assertSame('paid', $audit->new_values['payment_status']);
        $this->assertSame('pending', $audit->new_values['order_status']);
        $this->assertTrue($audit->new_values['sale_recognized']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'pending',
        ]);
    }
}

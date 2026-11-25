<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_order_creation_records_a_linked_audit_entry(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $categoryId = DB::table('menu_categories')->insertGetId([
            'name' => 'Audit Test Category',
            'description' => 'Test category',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menuItemId = DB::table('menu_items')->insertGetId([
            'menu_category_id' => $categoryId,
            'name' => 'Audit Test Meal',
            'description' => 'Test meal',
            'price' => 75,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($cashier)
            ->post(route('pos.store'), [
                'order_type' => 'takeaway',
                'items_json' => json_encode([
                    ['id' => $menuItemId, 'quantity' => 2],
                ]),
            ])
            ->assertRedirect(route('pos.index'));

        $order = Order::query()
            ->where('cashier_id', $cashier->id)
            ->latest('id')
            ->firstOrFail();

        $audit = AuditLog::query()
            ->where('action', 'order_created')
            ->where('auditable_type', $order->getMorphClass())
            ->where('auditable_id', $order->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($cashier->id, $audit->user_id);
        $this->assertSame($order->order_number, $audit->new_values['order_number']);
        $this->assertSame('takeaway', $audit->new_values['order_type']);
        $this->assertEquals(150, $audit->new_values['total']);

        $this->assertCount(1, $audit->new_values['items']);
        $this->assertSame('Audit Test Meal', $audit->new_values['items'][0]['name']);
        $this->assertSame(2, $audit->new_values['items'][0]['quantity']);
        $this->assertEquals(75, $audit->new_values['items'][0]['unit_price']);
    }
}


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ORDERS
         */
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->unique();
            $table->foreignId('cashier_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('order_type', ['dine_in', 'takeaway'])
                ->default('takeaway');

            $table->enum('status', [
                'pending',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('pending');

            $table->enum('payment_status', [
                'unpaid',
                'partially_paid',
                'paid',
                'refunded',
            ])->default('unpaid');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();

            $table->foreignId('served_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('served_at')->nullable();
            $table->timestamp('receipt_verified_at')->nullable();

            $table->index(['status', 'payment_status']);
            $table->index(['order_type', 'created_at']);
        });

        /*
         * ORDER ITEMS
         */
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->restrictOnDelete();

            // Preserve the item name and price at the time of sale.
            $table->string('item_name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->text('notes')->nullable();

            $table->index(['order_id', 'menu_item_id']);
        });

        /*
         * PAYMENTS
         */
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->enum('method', [
                'cash',
                'mobile_money',
                'card',
            ]);

            $table->decimal('amount', 12, 2);

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'refunded',
            ])->default('pending');

            $table->string('payment_reference')->nullable()->unique();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();

            $table->index(['order_id', 'status']);
        });

        /*
         * AUDIT LOGS
         */
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['user_id', 'created_at']);

            $table->dropColumn([
                'user_id',
                'action',
                'auditable_type',
                'auditable_id',
                'description',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['received_by']);
            $table->dropIndex(['order_id', 'status']);

            $table->dropColumn([
                'order_id',
                'method',
                'amount',
                'status',
                'payment_reference',
                'received_by',
                'paid_at',
                'metadata',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['menu_item_id']);
            $table->dropIndex(['order_id', 'menu_item_id']);

            $table->dropColumn([
                'order_id',
                'menu_item_id',
                'item_name',
                'unit_price',
                'quantity',
                'subtotal',
                'notes',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropForeign(['served_by']);
            $table->dropUnique(['order_number']);
            $table->dropIndex(['status', 'payment_status']);
            $table->dropIndex(['order_type', 'created_at']);

            $table->dropColumn([
                'order_number',
                'cashier_id',
                'order_type',
                'status',
                'payment_status',
                'subtotal',
                'discount',
                'tax',
                'total',
                'notes',
                'served_by',
                'served_at',
                'receipt_verified_at',
            ]);
        });
    }
};

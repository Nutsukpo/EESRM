<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Display payment history.
     */
    public function index(Request $request)
    {
        $payments = Payment::with(['order', 'receivedBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where(
                        'payment_reference',
                        'like',
                        "%{$search}%"
                    )->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where(
                            'order_number',
                            'like',
                            "%{$search}%"
                        );
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the full-payment form for an order.
     */
    public function create(Order $order)
    {
        $order->load('items');

        if (in_array($order->status, ['cancelled', 'served'], true)) {
            return redirect()
                ->route('orders.show', $order)
                ->with(
                    'error',
                    'Cancelled or served orders cannot receive payments.'
                );
        }

        if ($order->payment_status === 'refunded') {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Refunded orders cannot receive payments.');
        }

        $hasCompletedPayment = Payment::where('order_id', $order->id)
            ->where('status', 'completed')
            ->exists();

        if (
            $hasCompletedPayment ||
            $order->payment_status === 'paid'
        ) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'This order has already been paid.');
        }

        $totalAmount = (float) $order->total;

        if ($totalAmount <= 0) {
            return redirect()
                ->route('orders.show', $order)
                ->with(
                    'error',
                    'The order total must be greater than zero before payment.'
                );
        }

        return view('payments.create', compact(
            'order',
            'totalAmount'
        ));
    }

    /**
     * Record exactly one full payment and recognize the sale.
     */
    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'method' => [
                'required',
                Rule::in(['cash', 'mobile_money', 'card']),
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],
            'payment_reference' => [
                'nullable',
                'string',
                'max:255',
                'unique:payments,payment_reference',
            ],
        ]);

        $payment = DB::transaction(function () use (
            $validated,
            $order,
            $request
        ) {
            // Lock the order to prevent simultaneous duplicate payments.
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedOrder->status, ['cancelled', 'served'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Cancelled or served orders cannot receive payments.',
                ]);
            }

            if ($lockedOrder->payment_status === 'refunded') {
                throw ValidationException::withMessages([
                    'order' => 'Refunded orders cannot receive payments.',
                ]);
            }

            // A paid order must never receive another payment.
            $hasCompletedPayment = Payment::where(
                'order_id',
                $lockedOrder->id
            )
                ->where('status', 'completed')
                ->exists();

            if (
                $hasCompletedPayment ||
                $lockedOrder->payment_status === 'paid'
            ) {
                throw ValidationException::withMessages([
                    'amount' => 'This order has already been paid. Duplicate payments are not allowed.',
                ]);
            }

            // Compare money using integer pesewas to avoid float errors.
            $orderTotal = (int) round(
                (float) $lockedOrder->total * 100
            );

            $amount = (int) round(
                (float) $validated['amount'] * 100
            );

            if ($orderTotal <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'The order total must be greater than zero.',
                ]);
            }

            // Strict cash-and-carry: only the exact full amount is accepted.
            if ($amount !== $orderTotal) {
                throw ValidationException::withMessages([
                    'amount' => 'Full payment is required. Enter exactly GH₵'
                        . number_format($orderTotal / 100, 2)
                        . '. Partial payments and credit sales are not allowed.',
                ]);
            }

            $oldValues = [
                'order_status' => $lockedOrder->status,
                'payment_status' => $lockedOrder->payment_status,
            ];

            // Create one completed payment for this sale.
            $payment = Payment::create([
                'order_id' => $lockedOrder->id,
                'method' => $validated['method'],
                'amount' => number_format(
                    $amount / 100,
                    2,
                    '.',
                    ''
                ),
                'status' => 'completed',
                'payment_reference' => filled(
                    $validated['payment_reference'] ?? null
                )
                    ? trim($validated['payment_reference'])
                    : null,
                'received_by' => $request->user()?->id,
                'paid_at' => now(),
                'metadata' => [
                    'recorded_ip' => $request->ip(),
                    'sale_recognized' => true,
                ],
            ]);

            /*
             * IMPORTANT:
             * Do not set orders.status = 'paid'.
             * That value is not accepted by your current database column.
             * Preserve the operational status (for example, pending).
             * Payment status records whether the order has been paid.
             */
            $lockedOrder->update([
                'payment_status' => 'paid',
            ]);

            // Record the sale in the audit log within the same transaction.
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'sale_recorded',
                'auditable_type' => Payment::class,
                'auditable_id' => $payment->id,
                'description' => 'Full payment recorded for order '
                    . $lockedOrder->order_number,
                'old_values' => $oldValues,
                'new_values' => [
                    'payment_id' => $payment->id,
                    'order_id' => $lockedOrder->id,
                    'order_number' => $lockedOrder->order_number,
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'payment_reference' => $payment->payment_reference,
                    'payment_status' => $lockedOrder->payment_status,
                    'order_status' => $lockedOrder->status,
                    'sale_recognized' => true,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr(
                    (string) $request->userAgent(),
                    0,
                    1000
                ),
            ]);

            return $payment;
        }, 3);

        return redirect()
            ->route('payments.receipt', $payment)
            ->with(
                'success',
                'Full payment recorded successfully. The sale has been recognized.'
            );
    }

    /**
     * Display a receipt for a completed payment.
     */
    public function receipt(Payment $payment)
    {
        $payment->load([
            'order.items',
            'order.payments',
            'receivedBy',
        ]);

        abort_unless(
            $payment->status === 'completed',
            404
        );

        $paidAmount = (float) $payment->order->payments
            ->where('status', 'completed')
            ->sum('amount');

        return view('payments.receipt', compact(
            'payment',
            'paidAmount'
        ));
    }
}

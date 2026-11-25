
@extends('layouts.app')

@section('title', 'Payment Receipt')

@section('content')
<div class="container py-4 no-print-area">

    @if(session('success'))
        <div class="alert alert-success screen-only">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 screen-only">
        <div>
            <h2 class="fw-bold mb-1">Payment Receipt</h2>
            <p class="text-muted mb-0">Thermal receipt preview</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <select id="paper-width" class="form-select" style="width:auto;">
                <option value="80">80mm paper</option>
                <option value="58">58mm paper</option>
            </select>

            <button type="button" onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print me-1"></i> Print Receipt
            </button>
        </div>
    </div>

    <div class="receipt-stage">
        <div id="printable-receipt" class="receipt w80">

            <div class="r-center">
                <div class="r-title">EES RESTAURANT</div>
                <div class="r-small">Bolgatanga Stadium Road</div>
                <div class="r-small">Tel: 0542013350 / 0209133994</div>
                <div class="r-bold">PAYMENT RECEIPT</div>
            </div>

            <div class="r-sep-double"></div>

            <div class="r-row">
                <span>Receipt #</span>
                <strong>{{ $payment->payment_reference ?: 'PAY-' . $payment->id }}</strong>
            </div>

            <div class="r-row">
                <span>Order #</span>
                <strong>{{ $payment->order->order_number }}</strong>
            </div>

            <div class="r-row">
                <span>Date</span>
                <span>
                    {{ $payment->paid_at?->format('d/m/Y h:i A')
                        ?? $payment->created_at?->format('d/m/Y h:i A')
                        ?? '—' }}
                </span>
            </div>

            <div class="r-row">
                <span>Method</span>
                <span>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>
            </div>

            <div class="r-row">
                <span>Cashier</span>
                <span>{{ $payment->receivedBy?->name ?? '—' }}</span>
            </div>

            <div class="r-sep-double"></div>

            <table class="r-items-table">
                <thead>
                    <tr>
                        <th class="col-item">ITEM</th>
                        <th class="col-qty">QTY</th>
                        <th class="col-price">PRICE</th>
                        <th class="col-total">TOTAL</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payment->order->items as $item)
                        <tr>
                            <td class="col-item item-name">
                                {{ $item->item_name }}
                            </td>
                            <td class="col-qty">{{ $item->quantity }}</td>
                            <td class="col-price">
                                {{ number_format((float) $item->unit_price, 2) }}
                            </td>
                            <td class="col-total">
                                {{ number_format((float) $item->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-items">
                                No items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="r-sep-double"></div>

            <div class="r-row">
                <span>SUBTOTAL</span>
                <span>GH₵ {{ number_format((float) $payment->order->subtotal, 2) }}</span>
            </div>

            @if((float) $payment->order->discount > 0)
                <div class="r-row">
                    <span>DISCOUNT</span>
                    <span>- GH₵ {{ number_format((float) $payment->order->discount, 2) }}</span>
                </div>
            @endif

            @if((float) $payment->order->tax > 0)
                <div class="r-row">
                    <span>TAX</span>
                    <span>GH₵ {{ number_format((float) $payment->order->tax, 2) }}</span>
                </div>
            @endif

            <div class="r-sep"></div>

            <div class="r-row r-big">
                <span>ORDER TOTAL</span>
                <span>GH₵ {{ number_format((float) $payment->order->total, 2) }}</span>
            </div>

            <div class="r-row r-big">
                <span>AMOUNT PAID</span>
                <span>GH₵ {{ number_format((float) $payment->amount, 2) }}</span>
            </div>

            <div class="r-sep-double"></div>

            @if($payment->status === 'completed'
                && (int) round((float) $payment->amount * 100)
                    === (int) round((float) $payment->order->total * 100))
                <div class="r-center r-flag">
                    *** PAID IN FULL ***
                </div>
            @else
                <div class="r-center r-flag">
                    PAYMENT NOT CONFIRMED
                </div>
            @endif

            <div class="r-sep-double"></div>

            <div class="r-center">
                <div class="r-bold">Thank you for your business!</div>
                <div class="r-small">
                    Please retain this receipt for your records.
                </div>
            </div>

            <div class="r-feed">.</div>
        </div>
    </div>

    <div class="d-flex justify-content-center gap-2 mt-4 screen-only">
        <a href="{{ route('orders.show', $payment->order) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Order
        </a>

        <a href="{{ route('payments.index') }}"
           class="btn btn-success">
            <i class="fas fa-history me-1"></i> Payment History
        </a>
    </div>
</div>
@endsection

@push('styles')
<style id="receipt-styles">
    .receipt-stage {
        display: flex;
        justify-content: center;
        background: #e9ecef;
        padding: 24px 12px;
        border-radius: .5rem;
        overflow-x: auto;
    }

    .receipt {
        background: #fff;
        color: #000;
        font-family: 'Courier New', Courier, monospace;
        font-size: 12px;
        line-height: 1.35;
        padding: 4mm 3mm 6mm;
        box-sizing: border-box;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
        flex-shrink: 0;
    }

    .receipt.w80 { width: 72mm; }

    .receipt.w58 {
        width: 48mm;
        font-size: 10px;
        padding: 3mm 1.5mm 5mm;
    }

    .receipt * { box-sizing: border-box; }
    .r-center { text-align: center; }
    .r-title { font-size: 1.5em; font-weight: 800; letter-spacing: .5px; }
    .r-small { font-size: .85em; }
    .r-bold { font-weight: 700; }
    .r-big { font-size: 1.1em; font-weight: 800; }
    .r-flag { margin-top: 4px; font-weight: 700; font-size: .9em; }

    .r-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 4px;
    }

    .r-row > span:last-child,
    .r-row > strong:last-child {
        text-align: right;
        overflow-wrap: anywhere;
    }

    .r-sep { border-top: 1px dashed #000; margin: 5px 0; }
    .r-sep-double { border-top: 3px double #000; margin: 6px 0; }

    .r-items-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-family: 'Courier New', Courier, monospace;
        font-size: inherit;
        color: #000;
    }

    .r-items-table thead { border-bottom: 1px dashed #000; }

    .r-items-table th {
        font-weight: 800;
        padding: 3px 1px 4px;
        text-align: right;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .r-items-table td {
        padding: 4px 1px;
        text-align: right;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .r-items-table .col-item { width: 37%; text-align: left; }
    .r-items-table .col-qty { width: 12%; text-align: center; }
    .r-items-table .col-price { width: 24%; text-align: right; }
    .r-items-table .col-total { width: 27%; text-align: right; }
    .r-items-table .item-name { font-weight: 700; }
    .r-items-table .empty-items { text-align: center; padding: 8px 0; }

    .receipt.w58 .r-items-table { font-size: 9px; }
    .receipt.w58 .r-items-table th,
    .receipt.w58 .r-items-table td { padding-left: 0; padding-right: 0; }
    .receipt.w58 .r-items-table .col-item { width: 32%; }
    .receipt.w58 .r-items-table .col-qty { width: 12%; }
    .receipt.w58 .r-items-table .col-price { width: 27%; }
    .receipt.w58 .r-items-table .col-total { width: 29%; }

    .r-feed { height: 8mm; line-height: 8mm; text-align: center; color: #fff; overflow: hidden; }

    @media print {
        @page { margin: 0; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        body * { visibility: hidden !important; }

        #printable-receipt,
        #printable-receipt * {
            visibility: visible !important;
            color: #000 !important;
        }

        .receipt-stage {
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            overflow: visible !important;
            border-radius: 0 !important;
        }

        #printable-receipt {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            height: auto !important;
            margin: 0 !important;
            box-shadow: none !important;
            flex-shrink: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #printable-receipt.w80 {
            width: 72mm !important;
            padding: 2mm 0 4mm !important;
        }

        #printable-receipt.w58 {
            width: 48mm !important;
            padding: 2mm 0 4mm !important;
            font-size: 10px !important;
        }

        #printable-receipt.w58 .r-items-table { font-size: 9px !important; }

        .screen-only,
        .no-print-area > .alert { display: none !important; }

        .r-items-table { width: 100% !important; table-layout: fixed !important; }
        .r-items-table thead { display: table-header-group; }

        .r-items-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .r-items-table th,
        .r-items-table td { color: #000 !important; }
    }
</style>

<style id="page-size-style">
    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const receipt = document.getElementById('printable-receipt');
    const select = document.getElementById('paper-width');
    const pageStyle = document.getElementById('page-size-style');

    if (!receipt || !select || !pageStyle) {
        return;
    }

    function applyWidth(width) {
        width = String(width);

        if (!['58', '80'].includes(width)) {
            width = '80';
        }

        receipt.classList.remove('w80', 'w58');
        receipt.classList.add('w' + width);

        pageStyle.textContent =
            '@media print { @page { size: ' + width + 'mm auto; margin: 0; } }';

        select.value = width;

        try {
            localStorage.setItem('receipt_paper_width', width);
        } catch (error) {
            // Keep working when browser storage is unavailable.
        }
    }

    let savedWidth = '80';

    try {
        savedWidth = localStorage.getItem('receipt_paper_width') || '80';
    } catch (error) {
        // Use the default paper width.
    }

    applyWidth(savedWidth);

    select.addEventListener('change', function () {
        applyWidth(select.value);
    });
})();
</script>
@endpush

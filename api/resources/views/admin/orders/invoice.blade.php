<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; margin: 40px; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 24px; }
        .muted { color:#6b7280; }
        table { width:100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom:1px solid #e5e7eb; padding:10px 8px; text-align:left; }
        th:last-child, td:last-child { text-align:right; }
        .summary { width: 320px; margin-left:auto; margin-top: 24px; }
        .summary div { display:flex; justify-content:space-between; padding: 6px 0; }
        .grand { font-weight:700; font-size:18px; }
        @media print { .no-print { display:none; } body { margin: 20px; } }
    </style>
</head>
<body>
    <div class="top">
        <div>
            <h1 style="margin:0 0 8px;">Kanakshi.in</h1>
            <div class="muted">Invoice for order {{ $order->order_number }}</div>
        </div>
        <button class="no-print" onclick="window.print()">Print Invoice</button>
    </div>

    <div class="top">
        <div>
            <strong>Bill To</strong><br>
            {{ $order->ship_name }}<br>
            {{ $order->ship_email }}<br>
            {{ $order->ship_phone }}
        </div>
        <div>
            <strong>Ship To</strong><br>
            {!! nl2br(e($order->ship_address)) !!}<br>
            {{ $order->ship_city }}, {{ $order->ship_state }} - {{ $order->ship_pincode }}
        </div>
        <div>
            <strong>Order Date</strong><br>
            {{ $order->created_at->format('d M Y, h:i A') }}<br><br>
            <strong>Payment</strong><br>
            {{ strtoupper($order->payment_method) }} / {{ ucfirst($order->payment_status) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->price, 2) }}</td>
                    <td>₹{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
        <div><span>Discount</span><span>-₹{{ number_format($order->discount, 2) }}</span></div>
        <div><span>Tax</span><span>₹{{ number_format($order->tax, 2) }}</span></div>
        <div><span>Shipping</span><span>₹{{ number_format($order->shipping_cost, 2) }}</span></div>
        <div class="grand"><span>Grand Total</span><span>₹{{ number_format($order->total_amount, 2) }}</span></div>
    </div>
</body>
</html>

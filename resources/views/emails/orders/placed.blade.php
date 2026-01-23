<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .order-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-info td {
            padding: 8px 0;
            font-size: 14px;
        }
        .order-info td:first-child {
            font-weight: 600;
            color: #555;
            width: 40%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 6px;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 0;
            font-size: 14px;
        }
        .totals td:last-child {
            text-align: right;
        }
        .totals .total-row {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            border-top: 2px solid #dee2e6;
            padding-top: 12px !important;
        }
        .address-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .address-box h4 {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 600;
            color: #555;
        }
        .address-box p {
            margin: 5px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Order Confirmed!</h1>
            <p>Thank you for your purchase</p>
        </div>

        <div class="content">
            <p>Hi {{ $customer->full_name ?? $customer->email }},</p>
            <p>We've received your order and it's being processed. Here are your order details:</p>

            <div class="order-info">
                <table>
                    <tr>
                        <td>Order Number:</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Order Date:</td>
                        <td>{{ $order->created_at->format('F d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td>Payment Method:</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td>
                            <span style="
                                padding: 4px 8px;
                                border-radius: 4px;
                                font-size: 12px;
                                font-weight: 600;
                                background: {{ $order->payment_status === 'paid' ? '#d4edda' : '#fff3cd' }};
                                color: {{ $order->payment_status === 'paid' ? '#155724' : '#856404' }};
                            ">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <h3 style="margin-top: 30px; font-size: 18px;">Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->variant_name)
                            <br><small style="color: #666;">{{ $item->variant_name }}</small>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td>₹{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td style="color: #28a745;">-₹{{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Tax:</td>
                        <td>₹{{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td>
                            @if($order->shipping_amount > 0)
                                ₹{{ number_format($order->shipping_amount, 2) }}
                            @else
                                <span style="color: #28a745;">FREE</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Amount:</td>
                        <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <h3 style="margin-top: 30px; font-size: 18px;">Delivery Address</h3>
            <div class="address-box">
                @php
                    $shippingAddress = is_array($order->shipping_address) ? $order->shipping_address : [];
                @endphp
                @if(!empty($shippingAddress['full_name']))
                <p><strong>{{ $shippingAddress['full_name'] }}</strong></p>
                @endif
                @if(!empty($shippingAddress['phone']))
                <p>Phone: {{ $shippingAddress['phone'] }}</p>
                @endif
                @if(!empty($shippingAddress['address_line1']))
                <p>{{ $shippingAddress['address_line1'] }}</p>
                @endif
                @if(!empty($shippingAddress['address_line2']))
                <p>{{ $shippingAddress['address_line2'] }}</p>
                @endif
                @if(!empty($shippingAddress['landmark']))
                <p>Landmark: {{ $shippingAddress['landmark'] }}</p>
                @endif
                @if(!empty($shippingAddress['city']) && !empty($shippingAddress['state']) && !empty($shippingAddress['pincode']))
                <p>{{ $shippingAddress['city'] }}, {{ $shippingAddress['state'] }} {{ $shippingAddress['pincode'] }}</p>
                @endif
                @if(!empty($shippingAddress['country']))
                <p>{{ $shippingAddress['country'] }}</p>
                @endif
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/my-orders') }}" class="button">Track Your Order</a>
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions about your order, please contact our customer support.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This email was sent to {{ $customer->email }}</p>
        </div>
    </div>
</body>
</html>

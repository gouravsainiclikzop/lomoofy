<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Updated</title>
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
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header.processing {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .header.shipped {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .header.delivered {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .header.cancelled, .header.refunded {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0;
        }
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-badge.processing {
            background: #cfe2ff;
            color: #084298;
        }
        .status-badge.shipped {
            background: #d1e7dd;
            color: #0a3622;
        }
        .status-badge.delivered {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .status-badge.refunded {
            background: #fff3cd;
            color: #856404;
        }
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
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
        .timeline {
            margin: 30px 0;
        }
        .timeline-item {
            display: flex;
            margin-bottom: 20px;
        }
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .timeline-icon.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .timeline-icon.inactive {
            background: #e9ecef;
            color: #adb5bd;
        }
        .timeline-content h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .timeline-content p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
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
        <div class="header {{ $newStatus }}">
            <h1>
                @if($newStatus === 'processing')
                    📦 Order Processing
                @elseif($newStatus === 'shipped')
                    🚚 Order Shipped
                @elseif($newStatus === 'delivered')
                    ✅ Order Delivered
                @elseif($newStatus === 'cancelled')
                    ❌ Order Cancelled
                @elseif($newStatus === 'refunded')
                    💰 Order Refunded
                @else
                    Order Status Updated
                @endif
            </h1>
            <p>{{ $order->order_number }}</p>
        </div>

        <div class="content">
            <p>Hi {{ $customer->full_name ?? $customer->email }},</p>
            
            @if($newStatus === 'processing')
                <p>Great news! Your order is now being processed and will be shipped soon.</p>
            @elseif($newStatus === 'shipped')
                <p>Your order has been shipped and is on its way to you!</p>
            @elseif($newStatus === 'delivered')
                <p>Your order has been successfully delivered. We hope you enjoy your purchase!</p>
            @elseif($newStatus === 'cancelled')
                <p>Your order has been cancelled. If you didn't request this, please contact our support team.</p>
            @elseif($newStatus === 'refunded')
                <p>Your order has been refunded. The amount will be credited to your account within 5-7 business days.</p>
            @else
                <p>Your order status has been updated.</p>
            @endif

            <div style="text-align: center;">
                <span class="status-badge {{ $newStatus }}">
                    Status: {{ ucfirst($newStatus) }}
                </span>
            </div>

            <div class="order-info">
                <table>
                    <tr>
                        <td>Order Number:</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Order Date:</td>
                        <td>{{ $order->created_at->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Payment Status:</td>
                        <td>{{ ucfirst($order->payment_status) }}</td>
                    </tr>
                </table>
            </div>

            @if($newStatus === 'shipped' || $newStatus === 'delivered')
            <h3 style="font-size: 18px; margin-top: 30px;">Order Timeline</h3>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-icon active">✓</div>
                    <div class="timeline-content">
                        <h4>Order Placed</h4>
                        <p>{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon active">✓</div>
                    <div class="timeline-content">
                        <h4>Processing</h4>
                        <p>Order is being prepared</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon {{ in_array($newStatus, ['shipped', 'delivered']) ? 'active' : 'inactive' }}">
                        {{ in_array($newStatus, ['shipped', 'delivered']) ? '✓' : '○' }}
                    </div>
                    <div class="timeline-content">
                        <h4>Shipped</h4>
                        <p>{{ in_array($newStatus, ['shipped', 'delivered']) ? 'Order is on the way' : 'Pending' }}</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon {{ $newStatus === 'delivered' ? 'active' : 'inactive' }}">
                        {{ $newStatus === 'delivered' ? '✓' : '○' }}
                    </div>
                    <div class="timeline-content">
                        <h4>Delivered</h4>
                        <p>{{ $newStatus === 'delivered' ? 'Order delivered successfully' : 'Awaiting delivery' }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($newStatus !== 'cancelled' && $newStatus !== 'refunded')
            <div style="text-align: center;">
                <a href="{{ url('/my-orders') }}" class="button">View Order Details</a>
            </div>
            @endif

            @if($newStatus === 'delivered')
            <p style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 6px; border-left: 4px solid #0066cc;">
                <strong>Loved your purchase?</strong> We'd appreciate it if you could leave a review!
            </p>
            @endif

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions, please don't hesitate to contact our customer support.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This email was sent to {{ $customer->email }}</p>
        </div>
    </div>
</body>
</html>

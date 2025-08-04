<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }

        .company-details {
            color: #666;
            line-height: 1.4;
        }

        .invoice-info {
            text-align: right;
            flex: 1;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .invoice-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .invoice-details table {
            width: 100%;
        }

        .invoice-details td {
            padding: 3px 0;
        }

        .invoice-details td:first-child {
            font-weight: bold;
            color: #666;
        }

        .billing-section {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }

        .billing-info, .shipping-info {
            flex: 1;
            margin-right: 20px;
        }

        .shipping-info {
            margin-right: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .address-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .items-table th {
            background: #007bff;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }

        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .item-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #dee2e6;
        }

        .item-details {
            display: flex;
            align-items: center;
        }

        .item-info {
            margin-left: 10px;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .item-sku {
            color: #666;
            font-size: 10px;
        }

        .attribute-badge {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 3px;
            margin-bottom: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .summary-table {
            width: 300px;
        }

        .summary-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-table td:first-child {
            font-weight: bold;
        }

        .summary-table td:last-child {
            text-align: right;
            width: 100px;
        }

        .summary-total {
            background: #007bff;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .payment-info {
            margin-top: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }

        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .payment-icon {
            width: 30px;
            height: 30px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">Your E-Commerce Store</div>
                <div class="company-details">
                    123 Business Street<br>
                    City, State 12345<br>
                    Phone: +91 1234567890<br>
                    Email: info@yourstore.com<br>
                    GST: 12ABCDE3456F7GH
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-details">
                    <table>
                        <tr>
                            <td>Invoice No:</td>
                            <td>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                        <tr>
                            <td>Order Date:</td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td>
                                <span class="status-badge status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-section">
            <div class="billing-info">
                <div class="section-title">BILL TO</div>
                <div class="address-box">
                    <strong>{{ $order->user->name }}</strong><br>
                    {{ $order->user->email }}<br>
                    Phone: {{ $order->user->phone ?? 'N/A' }}
                </div>
            </div>
            <div class="shipping-info">
                <div class="section-title">SHIP TO</div>
                <div class="address-box">
                    <strong>{{ $order->user->name }}</strong><br>
                    {{ $order->address }}
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Product Details</th>
                    <th style="width: 20%;">Specifications</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 10%;" class="text-right">Unit Price</th>
                    <th style="width: 10%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="item-details">
                            @if($item->productVariation->product->images->count() > 0)
                                <img src="{{ public_path('storage/' . $item->productVariation->product->images->first()->image_path) }}" 
                                     alt="{{ $item->productVariation->product->name }}"
                                     class="item-image">
                            @else
                                <div style="width: 40px; height: 40px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    IMG
                                </div>
                            @endif
                            <div class="item-info">
                                <div class="item-name">{{ $item->productVariation->product->name }}</div>
                                <div class="item-sku">SKU: {{ $item->productVariation->sku ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($item->productVariation->attributeValues && $item->productVariation->attributeValues->count() > 0)
                            @foreach($item->productVariation->attributeValues as $value)
                                <span class="attribute-badge">
                                    {{ $value->attribute->name }}: {{ $value->value }}
                                </span>
                            @endforeach
                        @else
                            @php
                                $variations = [];
                                if($item->productVariation->size) $variations[] = 'Size: ' . $item->productVariation->size;
                                if($item->productVariation->color) $variations[] = 'Color: ' . $item->productVariation->color;
                                if($item->productVariation->fabric) $variations[] = 'Material: ' . $item->productVariation->fabric;
                            @endphp
                            @if(count($variations) > 0)
                                @foreach($variations as $variation)
                                    <span class="attribute-badge">{{ $variation }}</span>
                                @endforeach
                            @else
                                <span style="color: #666;">Default</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">₹{{ number_format($item->qty * $item->price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Order Summary -->
        <div class="summary-section">
            <table class="summary-table">
                @php
                    $subtotal = $order->items->sum(function($item) { return $item->qty * $item->price; });
                    $discount = $subtotal - $order->total;
                @endphp
                
                <tr>
                    <td>Subtotal ({{ $order->items->sum('qty') }} items):</td>
                    <td>₹{{ number_format($subtotal, 2) }}</td>
                </tr>
                
                @if($discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td style="color: #28a745;">-₹{{ number_format($discount, 2) }}</td>
                </tr>
                @endif
                
                <tr>
                    <td>Shipping & Handling:</td>
                    <td style="color: #28a745;">FREE</td>
                </tr>
                
                <tr>
                    <td>Tax (GST):</td>
                    <td>₹0.00</td>
                </tr>
                
                <tr class="summary-total">
                    <td>GRAND TOTAL:</td>
                    <td>₹{{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Information -->
        <div class="payment-info">
            <div class="section-title">PAYMENT INFORMATION</div>
            <div class="payment-method">
                <div class="payment-icon">₹</div>
                <div>
                    <strong>Payment Method:</strong> 
                    @if($order->payment_method == 'cod')
                        Cash on Delivery (COD)
                    @else
                        {{ ucfirst($order->payment_method) }}
                    @endif
                </div>
            </div>
            <p style="color: #666; margin-top: 10px;">
                @if($order->payment_method == 'cod')
                    Please keep the exact amount ready for delivery. Our delivery partner will collect the payment upon delivery.
                @else
                    Payment has been processed successfully through {{ ucfirst($order->payment_method) }}.
                @endif
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>For any queries regarding this invoice, please contact us at info@yourstore.com or call +91 1234567890</p>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">
                This is a computer generated invoice and does not require physical signature.
            </p>
        </div>
    </div>
</body>
</html>

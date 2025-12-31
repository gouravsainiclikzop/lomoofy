<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $orderData['order_number'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }
        .invoice-container {
            padding: 1.5rem;
            background: white;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #000;
        }
        .invoice-company-logo-section {
            flex: 1;
        }
        .company-logo {
            max-height: 60px;
            max-width: 200px;
        }
        .company-logo-text {
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        .invoice-title-section {
            flex: 1;
            text-align: right;
        }
        .invoice-title-section h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }
        .invoice-title-section p {
            font-size: 12px;
            margin: 5px 0 0 0;
            color: #666;
        }
        .invoice-details-row {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #000;
        }
        .invoice-sold-by {
            flex: 1;
        }
        .invoice-addresses {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: right;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
            font-size: 13px;
        }
        .company-name {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .company-address, .customer-address, .customer-city, .customer-pincode {
            font-size: 12px;
            margin-bottom: 3px;
        }
        .customer-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
        }
        .billing-address, .shipping-address {
            text-align: right;
        }
        .invoice-order-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }
        .order-info-left, .order-info-right {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .invoice-items-table {
            width: 100%;
            margin: 1rem 0;
            border-collapse: collapse;
            font-size: 12px;
        }
        .invoice-items-table th,
        .invoice-items-table td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #000;
            vertical-align: top;
        }
        .invoice-items-table th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        .invoice-items-table td:nth-child(1),
        .invoice-items-table td:nth-child(4),
        .invoice-items-table td:nth-child(6) {
            text-align: center;
        }
        .invoice-items-table td:nth-child(3),
        .invoice-items-table td:nth-child(5),
        .invoice-items-table td:nth-child(7),
        .invoice-items-table td:nth-child(8) {
            text-align: right;
        }
        .subtotal-row td,
        .gst-row td,
        .discount-row td,
        .shipping-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .grand-total-row td {
            font-weight: bold !important;
            background-color: #e9ecef !important;
            border-top: 2px solid #000 !important;
        }
        .amount-in-words {
            margin: 1rem 0;
            padding: 10px;
            border: 1px solid #000;
            font-size: 12px;
        }
        .invoice-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .tax-note {
            font-size: 12px;
        }
        .signature-section {
            text-align: right;
        }
        .authorized-signatory {
            font-size: 12px;
            text-align: center;
            min-width: 200px;
        }
        .signature-image {
            margin: 10px 0;
        }
        .signature-image img {
            max-height: 60px;
            max-width: 200px;
            object-fit: contain;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print {
                display: none;
            }
            .invoice-container {
                padding: 0;
            }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Print / Save as PDF</button>
    
    <div class="invoice-container">
        @php
            $order = $orderData;
            $shippingAddress = $order['shipping_address'];
            $companySettings = $order['company_settings'];
            $customer = $order['customer'];
            
            // State GST codes mapping
            $stateGstCodes = [
                'Jammu And Kashmir' => '01', 'Himachal Pradesh' => '02', 'Punjab' => '03', 'Chandigarh' => '04',
                'Uttarakhand' => '05', 'Haryana' => '06', 'Delhi' => '07', 'Rajasthan' => '08', 'Uttar Pradesh' => '09',
                'Bihar' => '10', 'Sikkim' => '11', 'Arunachal Pradesh' => '12', 'Nagaland' => '13', 'Manipur' => '14',
                'Mizoram' => '15', 'Tripura' => '16', 'Meghalaya' => '17', 'Assam' => '18', 'West Bengal' => '19',
                'Jharkhand' => '20', 'Odisha' => '21', 'Chhattisgarh' => '22', 'Madhya Pradesh' => '23', 'Gujarat' => '24',
                'Daman And Diu' => '25', 'Dadra & Nagar Haveli and Daman & Diu' => '26', 'Maharashtra' => '27',
                'Andhra Pradesh' => '28', 'Karnataka' => '29', 'Goa' => '30', 'Lakshadweep' => '31', 'Kerala' => '32',
                'Tamil Nadu' => '33', 'Puducherry' => '34', 'Andaman And Nicobar islands' => '35', 'Telangana' => '36',
                'Ladakh' => '38'
            ];
            
            // Get state codes
            $companyState = $companySettings['state'] ?? 'Haryana';
            $companyStateCode = $stateGstCodes[$companyState] ?? '06';
            $customerState = $shippingAddress->state ?? 'Haryana';
            $customerStateCode = $stateGstCodes[$customerState] ?? '06';
            $isSameState = $companyStateCode === $customerStateCode;
            
            // Build company billing address - use address, city, state, pincode fields
            $companyBillingParts = [];
            if (!empty($companySettings['address'])) {
                $companyBillingParts[] = $companySettings['address'];
            }
            if (!empty($companySettings['city'])) {
                $companyBillingParts[] = $companySettings['city'];
            }
            if (!empty($companySettings['state'])) {
                $companyBillingParts[] = $companySettings['state'];
            }
            if (!empty($companySettings['pincode'])) {
                $companyBillingParts[] = $companySettings['pincode'];
            }
            $companyBillingAddress = !empty($companyBillingParts) ? implode(', ', $companyBillingParts) : '';
            
            // Build shipping address parts
            $addressParts = [];
            if (!empty($shippingAddress->address_line1 ?? '')) $addressParts[] = $shippingAddress->address_line1;
            if (!empty($shippingAddress->address_line2 ?? '')) $addressParts[] = $shippingAddress->address_line2;
            if (!empty($shippingAddress->landmark ?? '')) $addressParts[] = $shippingAddress->landmark;
            $address = !empty($addressParts) ? implode(', ', $addressParts) : '-';
            
            $cityStateParts = [];
            if (!empty($shippingAddress->city ?? '')) $cityStateParts[] = $shippingAddress->city;
            if (!empty($shippingAddress->state ?? '')) $cityStateParts[] = $shippingAddress->state;
            $cityState = !empty($cityStateParts) ? implode(', ', $cityStateParts) : '-';
            
            $pinCodeCountryParts = [];
            if (!empty($shippingAddress->pincode ?? '')) $pinCodeCountryParts[] = $shippingAddress->pincode;
            if (!empty($shippingAddress->country ?? '')) $pinCodeCountryParts[] = $shippingAddress->country;
            $pinCodeCountry = !empty($pinCodeCountryParts) ? implode(', ', $pinCodeCountryParts) : '-';
            
            // Format order date
            $orderDate = \Carbon\Carbon::parse($order['created_at'])->format('d/m/Y');
            
            // Calculate GST breakdown
            $cgstBreakdown = [];
            $sgstBreakdown = [];
            $igstBreakdown = [];
            $exclusiveGstAmount = 0;
            $invoiceItems = [];
            
            foreach ($order['items'] as $index => $item) {
                $gstType = $item['gst_type'] ?? true;
                $gstPercentage = $item['gst_percentage'] ?? 18;
                $itemTotal = floatval($item['total_price']);
                $unitPrice = floatval($item['unit_price']);
                $quantity = intval($item['quantity']);
                
                // Calculate net amount
                $netAmount = 0;
                if ($gstType) {
                    $netAmount = $itemTotal / (1 + $gstPercentage / 100);
                } else {
                    $netAmount = $itemTotal;
                }
                
                // Calculate GST amount
                $itemGstAmount = 0;
                $cgstAmount = 0;
                $sgstAmount = 0;
                $igstAmount = 0;
                
                if ($gstPercentage > 0) {
                    if ($gstType) {
                        $itemGstAmount = $itemTotal - $netAmount;
                    } else {
                        $itemGstAmount = $netAmount * ($gstPercentage / 100);
                        $exclusiveGstAmount += $itemGstAmount;
                    }
                    
                    if ($isSameState) {
                        $cgstAmount = $itemGstAmount / 2;
                        $sgstAmount = $itemGstAmount / 2;
                        $cgstKey = 'CGST ' . ($gstPercentage/2) . '%';
                        $sgstKey = 'SGST ' . ($gstPercentage/2) . '%';
                        $cgstBreakdown[$cgstKey] = ($cgstBreakdown[$cgstKey] ?? 0) + $cgstAmount;
                        $sgstBreakdown[$sgstKey] = ($sgstBreakdown[$sgstKey] ?? 0) + $sgstAmount;
                    } else {
                        $igstAmount = $itemGstAmount;
                        $igstKey = 'IGST ' . $gstPercentage . '%';
                        $igstBreakdown[$igstKey] = ($igstBreakdown[$igstKey] ?? 0) + $igstAmount;
                    }
                }
                
                // Format GST display
                $gstDisplay = '-';
                if ($gstPercentage > 0) {
                    if ($isSameState) {
                        $gstDisplay = 'CGST ' . number_format($gstPercentage/2, 1) . '%<br>SGST ' . number_format($gstPercentage/2, 1) . '%';
                    } else {
                        $gstDisplay = 'IGST ' . $gstPercentage . '%';
                    }
                }
                
                $invoiceItems[] = [
                    'index' => $index + 1,
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'] ?? '',
                    'hsn_code' => $item['hsn_code'] ?? '4202',
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'net_amount' => $netAmount,
                    'gst_display' => $gstDisplay,
                    'gst_amount' => $itemGstAmount,
                    'total' => $netAmount + $itemGstAmount,
                ];
            }
            
            // Calculate grand total
            $subtotal = floatval($order['subtotal']);
            $discount = floatval($order['discount_amount'] ?? 0);
            $shipping = floatval($order['shipping_amount'] ?? 0);
            $calculatedGrandTotal = $subtotal - $discount + $exclusiveGstAmount + $shipping;
            
            // Number to words function
            function numberToWords($amount) {
                $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
                $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
                $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
                
                $convertHundreds = function($num) use (&$ones, &$teens, &$tens) {
                    $result = '';
                    if ($num > 99) {
                        $result .= $ones[intval($num / 100)] . ' Hundred ';
                        $num %= 100;
                    }
                    if ($num >= 20) {
                        $result .= $tens[intval($num / 10)] . ' ';
                        $num %= 10;
                    } else if ($num >= 10) {
                        $result .= $teens[$num - 10] . ' ';
                        return $result;
                    }
                    if ($num > 0) {
                        $result .= $ones[$num] . ' ';
                    }
                    return $result;
                };
                
                if ($amount == 0) return 'Zero Only';
                
                $num = floor($amount);
                $result = '';
                
                if ($num >= 10000000) {
                    $result .= $convertHundreds(intval($num / 10000000)) . 'Crore ';
                    $num %= 10000000;
                }
                if ($num >= 100000) {
                    $result .= $convertHundreds(intval($num / 100000)) . 'Lakh ';
                    $num %= 100000;
                }
                if ($num >= 1000) {
                    $result .= $convertHundreds(intval($num / 1000)) . 'Thousand ';
                    $num %= 1000;
                }
                if ($num > 0) {
                    $result .= $convertHundreds($num);
                }
                
                $decimal = round(($amount - floor($amount)) * 100);
                if ($decimal > 0) {
                    $result .= 'and ' . $convertHundreds($decimal) . 'Paise ';
                }
                
                return trim($result) . ' Only';
            }
            
            $amountInWords = numberToWords($calculatedGrandTotal);
        @endphp
        
        <div class="invoice-header">
            <div class="invoice-company-logo-section">
                @if(!empty($companySettings['company_logo']))
                    <img src="{{ $companySettings['company_logo'] }}" alt="Company Logo" class="company-logo">
                @else
                    <div class="company-logo-text">{{ $companySettings['company_logo_text'] ?? 'Lomoofy' }}</div>
                @endif
            </div>
            <div class="invoice-title-section">
                <h2>Tax Invoice</h2>
                <p>(Original for Recipient)</p>
            </div>
        </div>
        
        <div class="invoice-details-row">
            <div class="invoice-sold-by">
                <div class="section-title">Sold By :</div>
                <div class="company-name">{{ $companySettings['company_name'] ?? '' }}</div>
                <div class="company-address">{{ $companyBillingAddress }}</div>
                <div class="company-country">IN</div>
                <br>
                <div><strong>PAN No:</strong> {{ $companySettings['pan_no'] ?? '' }}</div>
                <div><strong>GST Registration No:</strong> {{ $companySettings['gst_registration_no'] ?? '' }}</div>
            </div>
            <div class="invoice-addresses">
                <div class="billing-address">
                    <div class="section-title">Billing Address :</div>
                    <div class="customer-name">{{ $customer['full_name'] }}</div>
                    <div class="customer-address">{{ $address }}</div>
                    <div class="customer-city">{{ $cityState }}</div>
                    <div class="customer-pincode">{{ $pinCodeCountry }}</div>
                    <div><strong>State/UT Code:</strong> {{ $customerStateCode }}</div>
                    <div><strong>Country Code:</strong> IN</div>
                </div>
                <div class="shipping-address">
                    <div class="section-title">Shipping Address :</div>
                    <div class="customer-address">{{ $address }}</div>
                    <div class="customer-city">{{ $cityState }}</div>
                    <div class="customer-pincode">{{ $pinCodeCountry }}</div>
                    <div><strong>State/UT Code:</strong> {{ $customerStateCode }}</div>
                    <div><strong>Country Code:</strong> IN</div>
                    <div><strong>Place of supply:</strong> {{ $shippingAddress->state ?? '' }}</div>
                    <div><strong>Place of delivery:</strong> {{ $shippingAddress->state ?? 'HARYANA' }}</div>
                </div>
            </div>
        </div>
        
        <div class="invoice-order-details">
            <div class="order-info-left">
                <div><strong>Order Number:</strong> {{ $order['order_number'] }}</div>
                <div><strong>Order Date:</strong> {{ $orderDate }}</div>
            </div>
            <div class="order-info-right">
                <div><strong>Invoice Number:</strong> YNVU-{{ $order['id'] }}</div>
                <div><strong>Invoice Details:</strong> DL-YNVU-{{ $order['id'] }}-{{ str_replace('/', '', $orderDate) }}</div>
                <div><strong>Invoice Date:</strong> {{ $orderDate }}</div>
            </div>
        </div>
        
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th>Sl. No</th>
                    <th>Description</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Net Amount</th>
                    <th>Tax Rate</th>
                    <th>Tax Amount</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceItems as $item)
                    <tr>
                        <td>{{ $item['index'] }}</td>
                        <td>
                            {{ $item['product_name'] }}{{ !empty($item['variant_name']) ? ' | ' . $item['variant_name'] : '' }}<br>
                            <small>HSN:{{ $item['hsn_code'] }}</small>
                        </td>
                        <td>₹{{ number_format($item['unit_price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>₹{{ number_format($item['net_amount'], 2) }}</td>
                        <td>{!! $item['gst_display'] !!}</td>
                        <td>₹{{ number_format($item['gst_amount'], 2) }}</td>
                        <td>₹{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
                
                <tr class="subtotal-row">
                    <td colspan="7" style="text-align: right; font-weight: bold;">Subtotal:</td>
                    <td style="font-weight: bold;">₹{{ number_format($subtotal, 2) }}</td>
                </tr>
                
                @if($isSameState)
                    @foreach($cgstBreakdown as $key => $amount)
                        <tr class="gst-row">
                            <td colspan="7" style="text-align: right; font-weight: bold;">{{ $key }}:</td>
                            <td style="font-weight: bold;">₹{{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($sgstBreakdown as $key => $amount)
                        <tr class="gst-row">
                            <td colspan="7" style="text-align: right; font-weight: bold;">{{ $key }}:</td>
                            <td style="font-weight: bold;">₹{{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    @foreach($igstBreakdown as $key => $amount)
                        <tr class="gst-row">
                            <td colspan="7" style="text-align: right; font-weight: bold;">{{ $key }}:</td>
                            <td style="font-weight: bold;">₹{{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
                
                @if($discount > 0)
                    <tr class="discount-row">
                        <td colspan="7" style="text-align: right; font-weight: bold;">Discount:</td>
                        <td style="font-weight: bold;">-₹{{ number_format($discount, 2) }}</td>
                    </tr>
                @endif
                
                @if($shipping > 0)
                    <tr class="shipping-row">
                        <td colspan="7" style="text-align: right; font-weight: bold;">Shipping Charges:</td>
                        <td style="font-weight: bold;">₹{{ number_format($shipping, 2) }}</td>
                    </tr>
                @endif
                
                <tr class="grand-total-row">
                    <td colspan="7" style="text-align: right; font-weight: bold; background-color: #f8f9fa;">Total:</td>
                    <td style="font-weight: bold; background-color: #f8f9fa;">₹{{ number_format($calculatedGrandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="amount-in-words">
            <strong>Amount in Words:</strong><br>
            {{ $amountInWords }}
        </div>
        
        <div class="invoice-footer">
            <div class="tax-note">
                <p>Whether tax is payable under reverse charge - No</p>
            </div>
            <div class="signature-section">
                <div class="authorized-signatory">
                    <p>For {{ $companySettings['company_name'] ?? '' }}:</p>
                    <br>
                    @if(!empty($companySettings['authorized_signatory']))
                        <div class="signature-image">
                            <img src="{{ $companySettings['authorized_signatory'] }}" alt="Authorized Signature">
                        </div>
                    @else
                        <br><br>
                    @endif
                    <p>Authorized Signatory</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-muted">Thank you for choosing us.</p>
        </div>
    </div>
</body>
</html>

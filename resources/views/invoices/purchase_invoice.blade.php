<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice['invoice_header']['title'] }} - {{ $invoice['invoice_header']['invoice_number'] }}</title>
    <style>
        @font-face {
            font-family: 'Tajawal';
            src: url("{{ public_path('fonts/Tajawal-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url("{{ public_path('fonts/Tajawal-Bold.ttf') }}") format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body, html, * {
            font-family: 'Tajawal', sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: right;
        }

        h1, h3, strong {
            font-weight: bold;
        }

        .container { width: 100%; margin: 0 auto; }
        .invoice-header { background-color: #f2f2f2; padding: 20px; text-align: center; border-bottom: 2px solid #ddd; }
        .invoice-header h1 { margin: 0; font-size: 24px; }
        .details-section { padding: 20px 0; overflow: hidden; }
        .details-section .supplier-details, .details-section .invoice-details { width: 48%; float: right; }
        .details-section .invoice-details { float: left; text-align: left; }
        .details-section h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f9f9f9; }
        .summary-section { margin-top: 20px; width: 40%; float: left; }
        .summary-section td { border: none; text-align: left;}
        .summary-section strong { float: right; }
        .notes { margin-top: 30px; font-size: 11px; color: #555; clear: both; }
    </style>
</head>
<body>
<div class="container">
    <div class="invoice-header">
        <h1>{{ $invoice['invoice_header']['title'] }}</h1>
    </div>

    <div class="details-section">
        <div class="supplier-details">
            <h3>{{ $invoice['supplier_details']['billed_from'] }}</h3>
            <p>
                <strong>{{ $invoice['supplier_details']['name'] }}</strong><br>
                {{ $invoice['supplier_details']['address'] }}<br>
                الهاتف: {{ $invoice['supplier_details']['phone'] }}<br>
                البريد الإلكتروني: {{ $invoice['supplier_details']['email'] }}
            </p>
        </div>
        <div class="invoice-details">
            <h3>{{ $invoice['order_details']['invoice_details'] }}</h3>
            <p>
                <strong>{{ $invoice['order_details']['invoice_no_label'] }}</strong> {{ $invoice['invoice_header']['invoice_number'] }}<br>
                <strong>{{ $invoice['order_details']['issue_date_label'] }}</strong> {{ $invoice['invoice_header']['issue_date'] }}<br>
                <strong>{{ $invoice['order_details']['due_date_label'] }}</strong> {{ $invoice['invoice_header']['due_date'] }}<br>
                <strong>{{ $invoice['order_details']['status_label'] }}</strong> {{ $invoice['order_details']['status'] }}
            </p>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>كود المادة</th>
            <th>اسم المادة</th>
            <th>الكمية</th>
            <th>الوحدة</th>
            <th>سعر الوحدة</th>
            <th>الإجمالي</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice['items_table'] as $item)
            <tr>
                <td>{{ $item['number'] }}</td>
                <td>{{ $item['item_code'] }}</td>
                <td>{{ $item['item_name'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ $item['unit_name'] }}</td>
                <td>{{ number_format($item['unit_price'], 2) }}</td>
                <td>{{ number_format($item['total_price'], 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <table>
            <tr>
                <td><strong>المجموع الكلي:</strong></td>
                <td><strong>{{ number_format($invoice['summary']['grand_total'], 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if($invoice['notes']['content'])
        <div class="notes">
            <h3>{{ $invoice['notes']['notes_label'] }}</h3>
            <p>{{ $invoice['notes']['content'] }}</p>
        </div>
    @endif
</div>
</body>
</html>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $report['report_header']['title'] }}</title>
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
            font-size: 11px;
            direction: rtl;
            text-align: right;
        }
        h1, strong { font-weight: bold; }
        .container { width: 100%; margin: 0 auto; }
        .report-header { background-color: #f2f2f2; padding: 15px; text-align: center; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .report-header h1 { margin: 0; font-size: 22px; }
        .report-header p { margin: 5px 0 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; word-wrap: break-word; }
        th { background-color: #f9f9f9; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="report-header">
        <h1>{{ $report['report_header']['title'] }}</h1>
        <p>تاريخ التقرير: {{ $report['report_header']['report_date'] }}</p>
    </div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>كود المادة</th>
            <th>اسم المادة</th>
            <th>تاريخ الانتهاء</th>
            <th>المورد</th>
            <th>الكمية بالدفعة</th>
            <th>أماكن التخزين</th>
        </tr>
        </thead>
        <tbody>
        @if($report['items_table']->isEmpty())
            <tr>
                <td colspan="7" style="text-align: center;">لا توجد مواد منتهية الصلاحية حالياً.</td>
            </tr>
        @else
            @foreach($report['items_table'] as $item)
                <tr>
                    <td>{{ $item['number'] }}</td>
                    <td>{{ $item['item_code'] }}</td>
                    <td>{{ $item['item_name'] }}</td>
                    <td style="color: red; font-weight: bold;">{{ $item['expiry_date'] }}</td>
                    <td>{{ $item['supplier_name'] }}</td>
                    <td>{{ $item['total_quantity'] }}</td>
                    <td>{{ $item['location'] }}</td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
</body>
</html>

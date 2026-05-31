<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حركة مادة | {{ $item->NAME }}</title>
    <style> body { font-family: Arial, sans-serif; padding: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 20px;} th, td { border: 1px solid #000; padding: 8px; text-align: center; } th { background-color: #f1f1f1; } @media print { button { display: none; } } </style>
</head>
<body onload="window.print()">
    <button onclick="window.print()" style="padding: 10px; margin-bottom: 20px;">طباعة التقرير</button>
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <h2>تقرير حركة الصنف (مشتريات ومبيعات)</h2>
        <h3>الصنف: {{ $item->NAME }} ({{ $item->barcode1 }})</h3>
        <p>للفترة من: {{ $fromDate }} إلى: {{ $toDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>نوع الحركة</th>
                <th>رقم الفاتورة</th>
                <th>الوحدة</th>
                <th>الكمية المُدخلة (شراء)</th>
                <th>الكمية المُخرجة (بيع)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalIn = 0; $totalOut = 0; @endphp
            @foreach($movements as $mov)
                @php 
                    // إذا كان TYPE_NUMBER = 2 (مشتريات) فهو دخول للمستودع، 1 (مبيعات) خروج
                    $type = $mov->invoice->TYPE_NUMBER;
                    $inQty = $type == 2 ? $mov->QTY : 0;
                    $outQty = $type == 1 ? $mov->QTY : 0;
                    $totalIn += $inQty;
                    $totalOut += $outQty;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov->invoice->DATE)->format('Y-m-d') }}</td>
                    <td>{{ $type == 1 ? 'مبيعات' : ($type == 2 ? 'مشتريات' : 'مرتجع') }}</td>
                    <td>{{ $mov->invoice->NUMBER }}</td>
                    <td>{{ $mov->UNITE }}</td>
                    <td style="color: green;">{{ $inQty > 0 ? $inQty : '-' }}</td>
                    <td style="color: red;">{{ $outQty > 0 ? $outQty : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f1f1f1; font-weight: bold;">
                <td colspan="4">الإجمالي خلال الفترة</td>
                <td style="color: green;">{{ $totalIn }}</td>
                <td style="color: red;">{{ $totalOut }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
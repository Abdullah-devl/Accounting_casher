<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ميزان المراجعة</title>
    <style> body { font-family: Arial, sans-serif; padding: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 20px;} th, td { border: 1px solid #000; padding: 8px; text-align: center; } th { background-color: #f1f1f1; } @media print { button { display: none; } } </style>
</head>
<body onload="window.print()">
    <button onclick="window.print()" style="padding: 10px; margin-bottom: 20px;">طباعة التقرير</button>
    <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <h2>ميزان المراجعة (بالمجاميع)</h2>
        <p>للفترة من: {{ $fromDate }} إلى: {{ $toDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>رقم الحساب</th>
                <th>اسم الحساب</th>
                <th>إجمالي الحركات المدينة</th>
                <th>إجمالي الحركات الدائنة</th>
            </tr>
        </thead>
        <tbody>
            @php $totalD = 0; $totalC = 0; @endphp
            @foreach($accounts as $acc)
                @if($acc->total_debit > 0 || $acc->total_credit > 0)
                    @php 
                        $totalD += $acc->total_debit; 
                        $totalC += $acc->total_credit; 
                    @endphp
                    <tr>
                        <td>{{ $acc->CODE }}</td>
                        <td>{{ $acc->NAME }}</td>
                        <td>{{ number_format($acc->total_debit, 2) }}</td>
                        <td>{{ number_format($acc->total_credit, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #e9ecef; font-weight: bold; font-size: 18px;">
                <td colspan="2">إجمالي ميزان المراجعة</td>
                <td style="color: blue;">{{ number_format($totalD, 2) }}</td>
                <td style="color: red;">{{ number_format($totalC, 2) }}</td>
            </tr>
            @if(round($totalD, 2) !== round($totalC, 2))
                <tr><td colspan="4" style="color: red; font-weight: bold; text-align: center;">يوجد خلل في توازن القيود!</td></tr>
            @endif
        </tfoot>
    </table>
</body>
</html>
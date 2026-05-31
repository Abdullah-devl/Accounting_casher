<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب | {{ $account->NAME }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f1f1f1; }
        @media print { button { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button onclick="window.print()" style="padding: 10px; margin-bottom: 20px;">طباعة التقرير</button>
    
    <div class="report-header">
        <h2>كشف حساب تفصيلي</h2>
        <h3>الحساب: {{ $account->NAME }} ({{ $account->CODE }})</h3>
        <p>للفترة من: {{ $fromDate }} إلى: {{ $toDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>رقم المستند</th>
                <th>البيان</th>
                <th>مدين (له)</th>
                <th>دائن (عليه)</th>
                <th>الرصيد التراكمي</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background: #fff3cd; font-weight: bold;">
                <td colspan="3">الرصيد الافتتاحي / السابق</td>
                <td>{{ $previousBalance > 0 ? number_format($previousBalance, 2) : '0.00' }}</td>
                <td>{{ $previousBalance < 0 ? number_format(abs($previousBalance), 2) : '0.00' }}</td>
                <td dir="ltr">{{ number_format($previousBalance, 2) }}</td>
            </tr>
            
            @php $runningBalance = $previousBalance; @endphp
            
            @foreach($transactions as $trans)
                @php 
                    // مدين يزيد الرصيد الإيجابي للحساب، دائن ينقصه
                    $runningBalance += ($trans->DEBIT - $trans->CREDIT); 
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($trans->entry->DATE)->format('Y-m-d') }}</td>
                    <td>{{ $trans->entry->NUMBER }}</td>
                    <td>{{ $trans->NOTE }}</td>
                    <td>{{ number_format($trans->DEBIT, 2) }}</td>
                    <td>{{ number_format($trans->CREDIT, 2) }}</td>
                    <td dir="ltr" style="font-weight: bold; color: {{ $runningBalance < 0 ? 'red' : 'green' }}">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f1f1f1; font-weight: bold;">
                <td colspan="3">الإجمالي النهائي</td>
                <td>{{ number_format($transactions->sum('DEBIT') + ($previousBalance > 0 ? $previousBalance : 0), 2) }}</td>
                <td>{{ number_format($transactions->sum('CREDIT') + ($previousBalance < 0 ? abs($previousBalance) : 0), 2) }}</td>
                <td dir="ltr">{{ number_format($runningBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

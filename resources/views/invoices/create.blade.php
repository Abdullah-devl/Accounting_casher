<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة الكاشير | النظام المحاسبي الأول</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
</head>
<body>

    <div class="pos-container">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e9ecef; padding-bottom: 10px; margin-bottom: 20px;">
            <h1 style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">نظام المبيعات - نقطة البيع</h1>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('shifts.index') }}" class="btn" style="background-color: #17a2b8; color: white; text-decoration: none; padding: 8px 16px; border-radius: 5px; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-box-open"></i> إدارة ورديتي</a>
                <a href="{{ url('/') }}" class="btn" style="background-color: #343a40; color: white; text-decoration: none; padding: 8px 16px; border-radius: 5px; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-home"></i> العودة للرئيسية</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf 

            <h3>1. البيانات الأساسية للفاتورة</h3>
            <div class="totals-row">
                <div>
                    <label>رقم الفاتورة:</label>
                    <input type="number" name="invoice_number" class="form-control" required style="width: 200px;">
                </div>
                <div>
                    <label>طريقة الدفع:</label>
                    <select name="payment_method" class="form-control" style="width: 200px;">
                        <option value="0">نقدي</option>
                        <option value="1">شبكة</option>
                        <option value="2">آجل</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>2. الأصناف</h3>
                <button type="button" id="btn-add-row" class="btn btn-primary">+ إضافة صنف يدوياً</button>
            </div>
            
            <table class="pos-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">الصنف / الباركود</th>
                        <th>الوحدة</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الضريبة %</th>
                        <th>الإجمالي</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody id="items-tbody">
                    <tr class="item-row">
                        <td>
                            <input type="text" name="items[0][item_id]" class="form-control input-item-id" placeholder="كود الصنف أو الباركود">
                            <input type="hidden" name="items[0][cost]" value="0">
                        </td>
                        <td><input type="text" name="items[0][unit]" class="form-control input-unit" value="حبة"></td>
                        <td><input type="number" name="items[0][qty]" class="form-control input-qty" value="1" min="1"></td>
                        <td><input type="number" name="items[0][price]" class="form-control input-price" value="0" min="0" step="0.01"></td>
                        <td><input type="number" name="items[0][vat]" class="form-control input-vat" value="15" readonly></td>
                        <td><input type="number" name="items[0][total]" class="form-control input-total" value="0" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-remove">حذف</button></td>
                    </tr>
                </tbody>
            </table>

            <div class="totals-section">
                <div class="totals-row">
                    <label>الإجمالي قبل الضريبة:</label>
                    <input type="number" name="total_amount" id="total_amount" class="form-control" value="0" readonly style="width: 300px;">
                </div>
                <div class="totals-row">
                    <label>إجمالي الضريبة (15%):</label>
                    <input type="number" name="total_vat" id="total_vat" class="form-control" value="0" readonly style="width: 300px;">
                </div>
                <div class="totals-row total-final">
                    <label>الصافي النهائي المطلوب دفعه:</label>
                    <input type="number" name="net_amount" id="net_amount" class="form-control" value="0" readonly style="width: 300px;">
                </div>
            </div>

            <input type="hidden" name="cash_account_id" value="acc-uuid-1">
            <input type="hidden" name="sales_account_id" value="acc-uuid-2">
            <input type="hidden" name="vat_account_id" value="acc-uuid-3">

            <button type="submit" class="btn btn-success">حفظ الفاتورة وترحيل القيد المحاسبي</button>
        </form>
    </div>

    <script src="{{ asset('js/pos.js') }}"></script>
</body>
</html>

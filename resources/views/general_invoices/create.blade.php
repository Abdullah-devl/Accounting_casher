@extends('layouts.app')

@section('title', 'إنشاء فاتورة جديدة')
@section('header_title', 'إدارة الفواتير العامة - إنشاء مستند')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/general_invoices.css') }}">
@endpush

@section('content')

<div class="invoice-container">
    <form id="generalInvoiceForm">
        @csrf
        
        <div class="invoice-header-section">
            <h3>البيانات الأساسية للفاتورة</h3>
            <div class="grid-4">
                <div class="form-group">
                    <label>نوع الفاتورة:</label>
                    <select name="type_id" id="type_id" class="form-control" required>
                        <option value="">-- اختر النوع --</option>
                        @foreach($types as $type)
                            <option value="{{ $type->GUID }}" data-vat="{{ $type->val_vat }}">{{ $type->NAME }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>رقم الفاتورة:</label>
                    <input type="number" name="invoice_number" id="invoice_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>تاريخ الفاتورة:</label>
                    <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>طريقة الدفع:</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        <option value="1">آجل (على الحساب)</option>
                        <option value="0">نقدي (عبر الصندوق)</option>
                        <option value="2">شبكة / بنك</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>حساب العميل / المورد:</label>
                    <select name="account_id" id="account_id" class="form-control" required>
                        <option value="">-- اختر الحساب من الدليل --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>البيان (ملاحظات):</label>
                    <input type="text" name="note" id="note" class="form-control" placeholder="شرح الفاتورة...">
                </div>
            </div>
        </div>

        <div class="invoice-details-section">
            <div class="section-header">
                <h3>الأصناف والمواد</h3>
                <button type="button" class="btn btn-sm btn-primary" onclick="addNewRow()"><i class="fas fa-plus"></i> سطر جديد</button>
            </div>
            
            <div class="table-responsive">
                <table class="editable-table" id="invoiceTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 30%;">رقم/اسم الصنف (بحث تلقائي)</th>
                            <th style="width: 10%;">الوحدة</th>
                            <th style="width: 10%;">الكمية</th>
                            <th style="width: 15%;">سعر الإفراد</th>
                            <th style="width: 10%;">الخصم</th>
                            <th style="width: 15%;">الإجمالي</th>
                            <th style="width: 5%;">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        </tbody>
                </table>
            </div>
            
            <datalist id="itemsList">
                @foreach($items as $item)
                    <option value="{{ $item->barcode1 }} - {{ $item->NAME }}" data-id="{{ $item->GUID }}" data-unit="{{ $item->UNITE1 }}" data-price="{{ $item->PRICE1 }}">
                @endforeach
            </datalist>
        </div>

        <div class="invoice-footer-section">
            <div class="totals-box">
                <div class="total-row">
                    <label>الإجمالي قبل الضريبة:</label>
                    <input type="text" id="sub_total" readonly class="total-input">
                </div>
                <div class="total-row">
                    <label>إجمالي الخصومات:</label>
                    <input type="text" id="total_discount" readonly class="total-input">
                </div>
                <div class="total-row">
                    <label>ضريبة القيمة المضافة:</label>
                    <input type="text" id="total_vat" readonly class="total-input">
                </div>
                <div class="total-row final-total">
                    <label>الصافي النهائي:</label>
                    <input type="text" id="net_amount" readonly class="total-input">
                </div>
            </div>
            
            <div class="actions-box">
                <button type="button" class="btn btn-success btn-lg" onclick="saveGeneralInvoice()"><i class="fas fa-save"></i> حفظ الفاتورة وترحيل القيد</button>
                <a href="{{ route('general_invoices.index') }}" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> إلغاء</a>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
    <script>
        const allItems = @json($items);
    </script>
    <script src="{{ asset('js/general_invoices.js') }}"></script>
@endpush
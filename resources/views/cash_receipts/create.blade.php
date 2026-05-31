@extends('layouts.app')

@section('title', 'سندات القبض والصرف')
@section('header_title', 'إدارة الصناديق - سندات القبض والصرف')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/receipts.css') }}">
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
@endif

<div class="receipt-container">
    <div class="receipt-header-actions">
        <button class="tab-btn active" id="btn-receipt" onclick="setReceiptType(1)">سند قبض (استلام)</button>
        <button class="tab-btn" id="btn-payment" onclick="setReceiptType(2)">سند صرف (دفع)</button>
        <a href="{{ route('cash_receipts.index') }}" class="btn-link"><i class="fas fa-list"></i> سجل السندات</a>
    </div>

    <form action="{{ route('cash_receipts.store') }}" method="POST" id="receiptForm" class="receipt-form">
        @csrf
        <input type="hidden" name="receipt_type" id="receipt_type" value="1">

        <div class="receipt-body">
            <div class="header-banner" id="banner">
                <h2 id="banner-title">سند قبض نقدي</h2>
                <div class="receipt-meta">
                    <div>
                        <label>رقم السند:</label>
                        <input type="number" name="receipt_number" value="{{ rand(1000, 9999) }}" required class="form-control" style="width: 150px; text-align: center;">
                    </div>
                    <div>
                        <label>التاريخ:</label>
                        <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required class="form-control">
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label id="lbl_cash_account">حساب الصندوق (المستلم):</label>
                    <select name="cash_account_id" class="form-control select2" required>
                        <option value="">-- اختر الصندوق/البنك --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label id="lbl_target_account">حساب العميل (الدافع):</label>
                    <select name="target_account_id" class="form-control select2" required>
                        <option value="">-- اختر الحساب الهدف --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="amount-section">
                <label>مبلغ وقدره:</label>
                <input type="number" name="amount" id="amount" class="amount-input" step="0.01" min="0.1" placeholder="0.00" required>
                <span class="currency">ريال</span>
            </div>

            <div class="form-group">
                <label>وذلك عن (البيان):</label>
                <textarea name="note" class="form-control" rows="2" placeholder="اكتب تفاصيل الدفعة أو الغرض منها..." required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> حفظ السند وترحيل القيد</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // تغيير واجهة السند ديناميكياً بناءً على الاختيار (قبض أو صرف)
    function setReceiptType(type) {
        document.getElementById('receipt_type').value = type;
        
        const btnReceipt = document.getElementById('btn-receipt');
        const btnPayment = document.getElementById('btn-payment');
        const banner = document.getElementById('banner');
        const bannerTitle = document.getElementById('banner-title');
        const lblCash = document.getElementById('lbl_cash_account');
        const lblTarget = document.getElementById('lbl_target_account');

        if (type === 1) { // سند قبض
            btnReceipt.classList.add('active');
            btnPayment.classList.remove('active');
            banner.style.borderRightColor = '#28a745';
            bannerTitle.innerText = 'سند قبض نقدي (استلام أموال)';
            lblCash.innerText = 'حساب الصندوق (المستلم للأموال):';
            lblTarget.innerText = 'حساب العميل/الإيراد (الدافع):';
        } else { // سند صرف
            btnPayment.classList.add('active');
            btnReceipt.classList.remove('active');
            banner.style.borderRightColor = '#dc3545';
            bannerTitle.innerText = 'سند صرف نقدي (دفع أموال)';
            lblCash.innerText = 'حساب الصندوق (الدافع للأموال):';
            lblTarget.innerText = 'حساب المورد/المصروف (المستلم):';
        }
    }
</script>
@endpush
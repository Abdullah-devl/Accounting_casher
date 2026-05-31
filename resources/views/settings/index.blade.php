@extends('layouts.app')

@section('title', 'إعدادات النظام')
@section('header_title', 'تهيئة النظام والبيانات الأساسية')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="tabs-header">
    <button class="tab-btn active" onclick="openTab(event, 'CompanyTab')"><i class="fas fa-building"></i> بيانات الشركة</button>
    <button class="tab-btn" onclick="openTab(event, 'BillTypesTab')"><i class="fas fa-file-invoice"></i> تهيئة الفواتير</button>
    <button class="tab-btn" onclick="openTab(event, 'PosTab')"><i class="fas fa-desktop"></i> أجهزة الكاشير</button>
</div>

<div id="CompanyTab" class="tab-content active">
    <h3>بيانات المنشأة الأساسية</h3>
    <form action="{{ route('settings.company.update') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>اسم الشركة / المؤسسة:</label>
            <input type="text" name="name_ar" class="form-control" value="{{ $company->NAMEAR }}" required>
        </div>
        <div class="form-group">
            <label>الرقم الضريبي:</label>
            <input type="text" name="vat_number" class="form-control" value="{{ $company->VAT }}">
        </div>
        <div class="form-group">
            <label>رقم السجل التجاري:</label>
            <input type="text" name="cr_number" class="form-control" value="{{ $company->CT }}">
        </div>
        <button type="submit" class="btn btn-primary">حفظ بيانات الشركة</button>
    </form>
</div>

<div id="BillTypesTab" class="tab-content">
    <h3>أنواع الفواتير المعرفة</h3>
    <table>
        <thead>
            <tr>
                <th>اسم الفاتورة</th>
                <th>حساب المبيعات/المشتريات</th>
                <th>حساب الصندوق</th>
                <th>حساب الضريبة</th>
                <th>الضريبة مفعلة؟</th>
                <th>تظهر بالكاشير؟</th>
            </tr>
        </thead>
        <tbody>
            @foreach($typeBills as $bill)
            <tr>
                <td>{{ $bill->NAME }}</td>
                <td>{{ $accounts->where('GUID', $bill->day_item)->first()->NAME ?? 'غير محدد' }}</td>
                <td>{{ $accounts->where('GUID', $bill->cash_day)->first()->NAME ?? 'غير محدد' }}</td>
                <td>{{ $accounts->where('GUID', $bill->cash_vat)->first()->NAME ?? 'غير محدد' }}</td>
                <td>{{ $bill->vat_activty ? 'نعم' : 'لا' }}</td>
                <td>{{ $bill->TYPE ? 'نعم' : 'لا' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr style="margin: 30px 0; border: 1px solid #eee;">
    
    <h3>إضافة نوع فاتورة جديد (التوجيه المحاسبي)</h3>
    <form action="{{ route('settings.type-bill.store') }}" method="POST">
        @csrf
        <div class="grid-2">
            <div class="form-group">
                <label>اسم الفاتورة (مثال: مبيعات جملة):</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>نوع الفاتورة:</label>
                <select name="type_code" class="form-control">
                    <option value="1">مبيعات</option>
                    <option value="2">مشتريات</option>
                    <option value="3">مرتجع مبيعات</option>
                </select>
            </div>
            <div class="form-group">
                <label>الحساب المقابل (مبيعات/مشتريات):</label>
                <select name="day_item_account" class="form-control">
                    <option value="">-- اختر الحساب من الدليل --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>حساب الصندوق الافتراضي:</label>
                <select name="cash_day_account" class="form-control">
                    <option value="">-- اختر الصندوق --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->GUID }}">{{ $acc->NAME }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>حساب الضريبة المحصلة/المدفوعة:</label>
                <select name="cash_vat_account" class="form-control">
                    <option value="">-- اختر حساب الضريبة --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->GUID }}">{{ $acc->NAME }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group checkbox-group">
                <label><input type="checkbox" name="vat_active" checked> تفعيل الضريبة التلقائية</label>
                <label><input type="checkbox" name="is_pos" checked> إظهارها في شاشة الكاشير</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">حفظ واعتماد التوجيه</button>
    </form>
</div>

<div id="PosTab" class="tab-content">
    <h3>أجهزة نقاط البيع المعرفة مسبقاً</h3>
    <table>
        <thead>
            <tr>
                <th>اسم الجهاز</th>
                <th>نوع فاتورة المبيعات الافتراضية</th>
                <th>حساب الصندوق المربوط بالجهاز</th>
                <th>اسم الطابعة الحرارية</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posDevices as $device)
            <tr>
                <td>{{ $device->NAME }}</td>
                <td>{{ $typeBills->where('GUID', $device->GUID_SALE)->first()->NAME ?? 'غير محدد' }}</td>
                <td>{{ $accounts->where('GUID', $device->ACCOUNT_CASH)->first()->NAME ?? 'غير محدد' }}</td>
                <td>{{ $device->PRINTER ?? 'بدون طابعة' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">لم يتم تعريف أي أجهزة بعد.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <hr style="margin: 30px 0; border: 1px solid #eee;">
    
    <h3>تعريف جهاز كاشير جديد</h3>
    <form action="{{ route('settings.pos-device.store') }}" method="POST">
        @csrf
        <div class="grid-2">
            <div class="form-group">
                <label>اسم الجهاز (مثال: كاشير المعرض الرئيسي):</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>نوع فاتورة المبيعات المعتمدة لهذا الجهاز:</label>
                <select name="default_sales_type" class="form-control" required>
                    <option value="">-- اختر نوع الفاتورة --</option>
                    @foreach($typeBills as $bill)
                        @if($bill->TYPE)
                            <option value="{{ $bill->GUID }}">{{ $bill->NAME }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>حساب الصندوق المرتبط بالجهاز:</label>
                <select name="cash_account_id" class="form-control" required>
                    <option value="">-- اختر حساب الصندوق --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>اسم الطابعة في نظام الويندوز (Printer Name):</label>
                <input type="text" name="printer_name" class="form-control" placeholder="مثال: XP-80C">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 15px;">حفظ واعتماد الجهاز</button>
    </form>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
@endpush
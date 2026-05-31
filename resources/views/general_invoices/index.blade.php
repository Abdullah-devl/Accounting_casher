@extends('layouts.app')

@section('title', 'سجل الفواتير العامة')
@section('header_title', 'الفواتير والإدارة - سجل المستندات')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/general_invoices.css') }}">
    <style>
        .invoices-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .invoices-table th, .invoices-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        .invoices-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .invoices-table tr:hover {
            background-color: #f9f9f9;
        }
        .btn-add-invoice {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-add-invoice:hover {
            background-color: #0b5ed7;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .badge-method {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-cash { background: #d1e7dd; color: #0f5132; }
        .badge-card { background: #cff4fc; color: #055160; }
        .badge-credit { background: #fff3cd; color: #664d03; }
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
<div class="invoice-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>سجل الفواتير العامة للإدارة</h3>
        <a href="{{ route('general_invoices.create') }}" class="btn-add-invoice">
            <i class="fas fa-plus"></i> إنشاء فاتورة جديدة
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success" style="padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="filter-section">
        <form action="{{ route('general_invoices.index') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; font-size: 14px;">رقم الفاتورة:</label>
                <input type="number" name="invoice_number" value="{{ request('invoice_number') }}" class="form-control" placeholder="بحث برقم الفاتورة...">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px; font-size: 14px;">نوع الفاتورة:</label>
                <select name="type_id" class="form-control">
                    <option value="">-- الكل --</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 25px;"><i class="fas fa-search"></i> تصفية</button>
                <a href="{{ route('general_invoices.index') }}" class="btn btn-secondary" style="padding: 10px 20px; margin-right: 5px;"><i class="fas fa-undo"></i> إعادة تعيين</a>
            </div>
        </form>
    </div>

    <table class="invoices-table">
        <thead>
            <tr>
                <th>رقم الفاتورة</th>
                <th>نوع المستند</th>
                <th>التاريخ</th>
                <th>طريقة الدفع</th>
                <th>الحساب المقابل</th>
                <th>الصافي النهائي</th>
                <th>البيان</th>
                <th>خيارات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td style="font-weight: bold;">#{{ $invoice->NUMBER }}</td>
                    <td>{{ $invoice->typeBill->NAME ?? 'غير محدد' }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->DATE)->format('Y-m-d') }}</td>
                    <td>
                        @if($invoice->TYPE_PAY == 0)
                            <span class="badge-method badge-cash">نقدي</span>
                        @elseif($invoice->TYPE_PAY == 1)
                            <span class="badge-method badge-credit">آجل</span>
                        @else
                            <span class="badge-method badge-card">شبكة</span>
                        @endif
                    </td>
                    <td>{{ $invoice->account->NAME ?? 'غير محدد' }}</td>
                    <td style="font-weight: bold; color: #dc3545;">
                        {{ number_format($invoice->TOT_FINLY, 2) }} ر.س
                    </td>
                    <td>{{ $invoice->NOTE ?? 'بدون بيان' }}</td>
                    <td>
                        <form action="{{ route('general_invoices.destroy', $invoice->GUID) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف الفاتورة وجميع القيود المرتبطة بها؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" style="padding: 5px 10px;" title="حذف الفاتورة">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #777; padding: 30px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        لا توجد فواتير مطابقة للبحث.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $invoices->links() }}
    </div>
</div>
@endsection

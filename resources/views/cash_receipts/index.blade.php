@extends('layouts.app')

@section('title', 'سجل سندات القبض والصرف')
@section('header_title', 'السندات المالية - سجل السندات')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/receipts.css') }}">
    <style>
        .receipts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .receipts-table th, .receipts-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        .receipts-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .receipts-table tr:hover {
            background-color: #f9f9f9;
        }
        .badge-type {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-receipt {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-payment {
            background-color: #f8d7da;
            color: #842029;
        }
        .btn-add-receipt {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-add-receipt:hover {
            background-color: #218838;
        }
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
<div class="receipt-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>سجل السندات المالية (القبض والصرف)</h3>
        <a href="{{ route('cash_receipts.create') }}" class="btn-add-receipt">
            <i class="fas fa-plus"></i> إضافة سند جديد
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success" style="padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <table class="receipts-table">
        <thead>
            <tr>
                <th>رقم السند</th>
                <th>نوع السند</th>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>حساب الصندوق</th>
                <th>الحساب المقابل</th>
                <th>البيان / الشرح</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td style="font-weight: bold;">#{{ $receipt->NUMBER }}</td>
                    <td>
                        @if($receipt->TYPE_NUMBER == 1)
                            <span class="badge-type badge-receipt"><i class="fas fa-arrow-down"></i> سند قبض</span>
                        @else
                            <span class="badge-type badge-payment"><i class="fas fa-arrow-up"></i> سند صرف</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($receipt->DATE)->format('Y-m-d') }}</td>
                    <td style="font-weight: bold; color: #0d6efd;">
                        {{ number_format($receipt->VAL_VOCHERS, 2) }} ر.س
                    </td>
                    <td>{{ $receipt->account->NAME ?? 'غير محدد' }}</td>
                    <td>{{ $receipt->customer->NAME ?? 'غير محدد' }}</td>
                    <td>{{ $receipt->NOTE ?? 'بدون بيان' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #777; padding: 30px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        لا توجد سندات مالية مسجلة بعد.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $receipts->links() }}
    </div>
</div>
@endsection

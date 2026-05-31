@extends('layouts.app')

@section('title', 'سجل قيود اليومية')
@section('header_title', 'الحسابات العامة - سجل القيود')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/journal.css') }}">
    <style>
        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .entries-table th, .entries-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        .entries-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .entries-table tr:hover {
            background-color: #f9f9f9;
        }
        .badge-ref {
            background: #e2e8f0;
            color: #4a5568;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .btn-add-entry {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-add-entry:hover {
            background-color: #0b5ed7;
        }
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
<div class="journal-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>سجل القيود المحاسبية بالمنشأة</h3>
        <a href="{{ route('journal_entries.create') }}" class="btn-add-entry">
            <i class="fas fa-plus"></i> إضافة قيد تسوية يدوي
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success" style="padding: 15px; background: #d1e7dd; color: #0f5132; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <table class="entries-table">
        <thead>
            <tr>
                <th>رقم القيد</th>
                <th>تاريخ التسجيل</th>
                <th>البيان العام للقيد</th>
                <th>قيمة القيد (المدين/الدائن)</th>
                <th>قيد مرتبطة بـ</th>
                <th>تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td style="font-weight: bold;">#{{ $entry->NUMBER }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->DATE)->format('Y-m-d') }}</td>
                    <td>{{ $entry->NOTE ?? 'بدون بيان' }}</td>
                    <td style="color: #28a745; font-weight: bold;">
                        {{ number_format($entry->details->sum('DEBIT'), 2) }} ر.س
                    </td>
                    <td>
                        @if($entry->TYPE_NUMBER != 14)
                            <span class="badge-ref">مستند تلقائي</span>
                        @else
                            <span class="badge-ref" style="background: #feebc8; color: #c05621;">تسوية يدوية</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($entry->DATE)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #777; padding: 30px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        لا توجد قيود مسجلة بعد في النظام.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $entries->links() }}
    </div>
</div>
@endsection

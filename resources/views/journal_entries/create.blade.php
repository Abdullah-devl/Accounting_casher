@extends('layouts.app')

@section('title', 'إنشاء قيد يومية يدوي')
@section('header_title', 'الحسابات العامة - قيود اليومية')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/journal.css') }}">
@endpush

@section('content')

<div class="journal-container">
    <form id="journalForm">
        @csrf
        
        <div class="journal-header-section">
            <div class="header-banner">
                <h3><i class="fas fa-book"></i> قيد يومية عامة (تسوية يدوية)</h3>
                <div class="status-badge" id="balanceStatus">غير متزن</div>
            </div>
            
            <div class="grid-3">
                <div class="form-group">
                    <label>رقم القيد:</label>
                    <input type="number" id="entry_number" class="form-control text-center" value="{{ rand(5000, 9999) }}" required>
                </div>
                <div class="form-group">
                    <label>تاريخ القيد:</label>
                    <input type="date" id="entry_date" class="form-control text-center" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label>البيان العام للقيد:</label>
                    <input type="text" id="main_note" class="form-control" placeholder="مثال: قيد إثبات رواتب شهر مارس..." required>
                </div>
            </div>
        </div>

        <div class="journal-details-section">
            <div class="section-header">
                <h4>أطراف القيد (المدين والدائن)</h4>
                <button type="button" class="btn btn-sm btn-primary" onclick="addNewRow()"><i class="fas fa-plus"></i> إضافة سطر جديد</button>
            </div>
            
            <table class="journal-table" id="journalTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">الحساب المالي</th>
                        <th style="width: 15%;">مدين (من حـ/)</th>
                        <th style="width: 15%;">دائن (إلى حـ/)</th>
                        <th style="width: 35%;">شرح السطر (البيان)</th>
                        <th style="width: 5%;">حذف</th>
                    </tr>
                </thead>
                <tbody id="journalBody">
                    </tbody>
            </table>
            
            <datalist id="accountsList">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->CODE }} - {{ $acc->NAME }}" data-id="{{ $acc->GUID }}">
                @endforeach
            </datalist>
        </div>

        <div class="journal-footer-section">
            <div class="totals-area">
                <div class="total-box">
                    <span>إجمالي المدين:</span>
                    <input type="text" id="total_debit" readonly value="0.00">
                </div>
                <div class="total-box">
                    <span>إجمالي الدائن:</span>
                    <input type="text" id="total_credit" readonly value="0.00">
                </div>
                <div class="total-box diff-box" id="diffBox">
                    <span>الفرق:</span>
                    <input type="text" id="total_diff" readonly value="0.00">
                </div>
            </div>
            
            <div class="actions-area">
                <button type="button" class="btn btn-success btn-lg" onclick="saveJournal()"><i class="fas fa-save"></i> اعتماد وترحيل القيد</button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/journal.js') }}"></script>
@endpush
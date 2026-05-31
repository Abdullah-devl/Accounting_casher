@extends('layouts.app')

@section('title', 'دليل الحسابات')
@section('header_title', 'شجرة الدليل المحاسبي')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/accounts.css') }}">
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success"><i class="fas fa-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
@endif

<div class="accounts-container">
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openCreateModal('', 'الجذر الأساسي')">
            <i class="fas fa-plus"></i> إضافة حساب رئيسي جديد
        </button>
    </div>

    <div class="tree-wrapper">
        <ul class="root-tree">
            @foreach($accounts as $account)
                @include('accounts.partials.tree_node', ['account' => $account])
            @endforeach
        </ul>
    </div>
</div>

<div id="createModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>إضافة حساب جديد</h3>
            <span class="close-modal" onclick="closeModal('createModal')">&times;</span>
        </div>
        <form action="{{ route('accounts.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <p>الحساب الأب: <strong id="parentNameDisplay">--</strong></p>
                <input type="hidden" name="parent_id" id="parentIdInput">
                
                <div class="form-group">
                    <label>رقم الحساب (الكود):</label>
                    <input type="text" name="code" class="form-control" required placeholder="مثال: 1102">
                </div>
                <div class="form-group">
                    <label>اسم الحساب:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>نوع الحساب:</label>
                    <select name="account_type" class="form-control" required>
                        <option value="0">حساب رئيسي (تجميعي)</option>
                        <option value="1">حساب فرعي (يقبل قيود)</option>
                    </select>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>رصيد افتتاحي مدين:</label>
                        <input type="number" name="opening_debit" class="form-control" value="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>رصيد افتتاحي دائن:</label>
                        <input type="number" name="opening_credit" class="form-control" value="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">حفظ الحساب</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>تعديل الحساب</h3>
            <span class="close-modal" onclick="closeModal('editModal')">&times;</span>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>اسم الحساب الجديد:</label>
                    <input type="text" name="name" id="editNameInput" class="form-control" required>
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="is_frozen" id="editFrozenInput"> 
                        <span style="color:red; font-weight:bold;">تجميد الحساب (إيقاف التعامل معه)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">تحديث الحساب</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/accounts.js') }}"></script>
@endpush
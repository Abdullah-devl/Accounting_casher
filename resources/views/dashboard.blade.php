@extends('layouts.app')

@section('title', 'اللوحة الرئيسية - النظام المحاسبي الأول')
@section('header_title', 'لوحة التحكم والمؤشرات')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Welcome Card -->
    <div class="welcome-card">
        <h3>أهلاً بك، {{ auth()->user()->name }}!</h3>
        <p>النظام المحاسبي الأول لإدارة الحسابات والمبيعات ونقاط البيع المتكامل. يمكنك الوصول السريع إلى كافة العمليات والتقارير من خلال هذه اللوحة.</p>
    </div>

    @if(auth()->user()->role == 'admin')
        <!-- Stats Grid (Admin Only) -->
        <div class="stats-grid">
            <!-- Accounts Card -->
            <a href="{{ route('accounts.index') }}" class="stat-card stat-blue">
                <div class="stat-info">
                    <span class="stat-title">الحسابات بالدليل</span>
                    <span class="stat-value">{{ $stats['accounts_count'] }}</span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-sitemap"></i>
                </div>
            </a>

            <!-- Items Card -->
            <a href="{{ route('items.index') }}" class="stat-card stat-green">
                <div class="stat-info">
                    <span class="stat-title">المواد والمنتجات</span>
                    <span class="stat-value">{{ $stats['items_count'] }}</span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </a>

            <!-- Invoices Card -->
            <a href="{{ route('general_invoices.index') }}" class="stat-card stat-orange">
                <div class="stat-info">
                    <span class="stat-title">إجمالي الفواتير</span>
                    <span class="stat-value">{{ $stats['invoices_count'] }}</span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </a>

            <!-- Shifts Card -->
            <a href="{{ route('shifts.index') }}" class="stat-card stat-purple">
                <div class="stat-info">
                    <span class="stat-title">الورديات المسجلة</span>
                    <span class="stat-value">{{ $stats['shifts_count'] }}</span>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-box-open"></i>
                </div>
            </a>
        </div>

        <!-- Quick Actions Section -->
        <div class="quick-actions-section">
            <h4>الوصول السريع للعمليات</h4>
            <div class="actions-grid">
                <a href="{{ route('invoices.create') }}" class="action-btn">
                    <i class="fas fa-cash-register"></i>
                    <span>نقطة البيع</span>
                </a>
                <a href="{{ route('shifts.index') }}" class="action-btn">
                    <i class="fas fa-box-open"></i>
                    <span>إدارة الورديات</span>
                </a>
                <a href="{{ route('accounts.index') }}" class="action-btn">
                    <i class="fas fa-sitemap"></i>
                    <span>دليل الحسابات</span>
                </a>
                <a href="{{ route('items.index') }}" class="action-btn">
                    <i class="fas fa-cubes"></i>
                    <span>بطاقات المواد</span>
                </a>
                <a href="{{ route('general_invoices.index') }}" class="action-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>الفواتير العامة</span>
                </a>
                <a href="{{ route('cash_receipts.index') }}" class="action-btn">
                    <i class="fas fa-money-bill"></i>
                    <span>سندات القبض والصرف</span>
                </a>
                <a href="{{ route('journal_entries.index') }}" class="action-btn">
                    <i class="fas fa-book"></i>
                    <span>قيود اليومية</span>
                </a>
                <a href="{{ route('reports.index') }}" class="action-btn">
                    <i class="fas fa-chart-line"></i>
                    <span>التقارير المالية</span>
                </a>
            </div>
        </div>
    @else
        <!-- Cashier View -->
        <div class="quick-actions-section">
            <h4>العمليات المتاحة للكاشير</h4>
            <div class="actions-grid">
                <a href="{{ route('invoices.create') }}" class="action-btn" style="padding: 30px;">
                    <i class="fas fa-cash-register" style="font-size: 36px; color: #3b82f6;"></i>
                    <span style="font-size: 18px; margin-top: 10px;">فتح نقطة البيع (شاشة الكاشير)</span>
                </a>
                <a href="{{ route('shifts.index') }}" class="action-btn" style="padding: 30px;">
                    <i class="fas fa-box-open" style="font-size: 36px; color: #10b981;"></i>
                    <span style="font-size: 18px; margin-top: 10px;">إدارة الوردية الحالية والصندوق</span>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

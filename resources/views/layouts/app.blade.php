<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'نظام MgaSoft')</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    
    @stack('styles')
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <i class="fas fa-boxes"></i> MgaSoft POS
        </div>
        <ul>
            <li><a href="{{ route('invoices.create') }}" class="{{ request()->routeIs('invoices.create') ? 'active' : '' }}"><i class="fas fa-cash-register"></i> نقطة البيع (الكاشير)</a></li>
            <li><a href="{{ route('shifts.index') }}" class="{{ request()->routeIs('shifts.index') ? 'active' : '' }}"><i class="fas fa-box-open"></i> إدارة ورديتي</a></li>

            @if(auth()->check() && auth()->user()->role == 'admin')
                <hr style="border-color: #4b545c; margin: 15px 0;">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt"></i> اللوحة الرئيسية</a></li>
                <li><a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.index') ? 'active' : '' }}"><i class="fas fa-sitemap"></i> دليل الحسابات</a></li>
                <li><a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.index') ? 'active' : '' }}"><i class="fas fa-cubes"></i> بطاقات المواد</a></li>
                <li><a href="{{ route('general_invoices.index') }}" class="{{ request()->routeIs('general_invoices.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> الفواتير العامة</a></li>
                <li><a href="{{ route('cash_receipts.index') }}" class="{{ request()->routeIs('cash_receipts.*') ? 'active' : '' }}"><i class="fas fa-money-bill"></i> سندات القبض والصرف</a></li>
                <li><a href="{{ route('journal_entries.index') }}" class="{{ request()->routeIs('journal_entries.*') ? 'active' : '' }}"><i class="fas fa-book"></i> قيود اليومية</a></li>
                <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> التقارير المالية</a></li>
                <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}"><i class="fas fa-cogs"></i> الإعدادات</a></li>
            @endif
            
            <hr style="border-color: #4b545c; margin: 15px 0;">
            
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" style="color: #ff6b6b;">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </a>
                </form>
            </li>
        </ul>
    </aside>

    <div class="main-content">
        
        <header class="topbar">
            <div class="page-title">
                <h2>@yield('header_title', 'اللوحة الرئيسية')</h2>
            </div>
            
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>مرحباً، {{ auth()->user()->name ?? 'مستخدم' }}</span>
                <span style="font-size: 12px; color: #888; margin-right: 5px;">
                    ({{ auth()->check() && auth()->user()->role == 'admin' ? 'المدير' : 'كاشير' }})
                </span>
            </div>
        </header>

        <main class="content-wrapper">
            @yield('content')
        </main>

    </div>

    <script src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
    
</body>
</html>
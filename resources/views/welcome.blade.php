<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'النظام المحاسبي الأول') }}</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <!-- Navbar -->
    <header class="welcome-navbar">
        <a href="#" class="logo">
            <i class="fas fa-boxes"></i>
            <span>{{ config('app.name', 'النظام المحاسبي الأول') }}</span>
        </a>
        
        <div class="nav-links">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-nav-login">
                        اللوحة الرئيسية <i class="fas fa-tachometer-alt" style="margin-right: 6px;"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">
                        تسجيل الدخول <i class="fas fa-sign-in-alt" style="margin-right: 6px;"></i>
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section fade-in">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fas fa-shield-halved"></i> نظام محاسبي سحابي متكامل ومحمي
            </div>
            <h1 class="hero-title">
                النظام المحاسبي الأول لإدارة <span>المبيعات والحسابات</span>
            </h1>
            <p class="hero-subtitle">
                حل متكامل ومرن صُمم خصيصاً للمؤسسات والشركات والمحلات التجارية لإدارة نقاط البيع الذكية (الكاشير)، دليل الحسابات الشجري، الورديات، الفواتير، والتقارير المالية بدقة وسرعة متناهية.
            </p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary-action">
                        الدخول إلى لوحة التحكم <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary-action">
                        ابدأ العمل الآن <i class="fas fa-rocket" style="margin-right: 8px;"></i>
                    </a>
                @endauth
                <a href="{{ route('invoices.create') }}" class="btn-secondary-action">
                    شاشة الكاشير السريعة <i class="fas fa-cash-register" style="margin-right: 8px;"></i>
                </a>
            </div>
        </div>
        
        <div class="hero-graphic">
            <div class="graphic-bg-circle"></div>
            <div class="hero-dashboard-preview">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--primary);">
                        <i class="fas fa-chart-line" style="color: var(--accent);"></i> مؤشرات الأداء الحالية
                    </div>
                    <span style="font-size: 11px; background-color: rgba(16, 185, 129, 0.1); color: var(--success); padding: 2px 8px; border-radius: 20px; font-weight: 700;">نشط</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="background-color: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">إجمالي الفواتير</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--primary);">متصلة بالكامل</div>
                    </div>
                    <div style="background-color: var(--bg-light); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">دليل الحسابات</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--accent);">مهيأ ومنظم</div>
                    </div>
                </div>
                <div style="background-color: rgba(37, 99, 235, 0.03); border: 1px dashed var(--accent); padding: 12px; border-radius: var(--radius-sm); font-size: 12px; text-align: center; color: var(--accent); font-weight: 500;">
                    <i class="fas fa-circle-check"></i> متصل بنجاح بقاعدة البيانات السحابية (PostgreSQL)
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="section-header">
            <h2>مكونات النظام المحاسبي المتكاملة</h2>
            <p>تم تصميم كافة هذه الوحدات لتعمل بتوافق كامل ومترابط لتغطية كافة جوانب العمل التجاري والمحاسبي المتقدم</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3>شاشة نقاط البيع (الكاشير)</h3>
                <p>واجهة كاشير ذكية وسريعة، تدعم باركود المواد، تعليق الفواتير، وطباعة الإيصالات، بالإضافة لطرق الدفع المتعددة (نقدي، شبكة، مختلط).</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-sitemap"></i>
                </div>
                <h3>دليل الحسابات وشجرة المحاسبة</h3>
                <p>دليل شجري كامل ومرن لتصنيف الأصول، الخصوم، حقوق الملكية، الإيرادات، والمصروفات بدقة محاسبية 100%.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3>إدارة الورديات والصناديق</h3>
                <p>مراقبة كاملة لحركة المبيعات وصناديق الكاشير خلال الوردية، وتتبع تسليم المبالغ وحساب الفروقات أو العجز بدقة متناهية.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3>التقارير المالية والتحليلات</h3>
                <p>توليد فوري ومباشر لموازين المراجعة، كشوف الحسابات، وتقارير المبيعات والأرباح والضرائب المضافة لتسهيل اتخاذ القرار.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="welcome-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-boxes"></i>
                <span>{{ config('app.name', 'النظام المحاسبي الأول') }}</span>
            </div>
            <div class="footer-copyright">
                حقوق النشر &copy; 2026 - جميع الحقوق محفوظة لـ {{ config('app.name', 'النظام المحاسبي الأول') }}
            </div>
        </div>
    </footer>

</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'النظام المحاسبي الأول') }}</title>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Custom Auth Stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    </head>
    <body>
        <div class="login-wrapper">
            <!-- الجانب التعريفي (البانر) -->
            <div class="login-banner">
                <div class="banner-content">
                    <a href="{{ url('/') }}" class="banner-logo">
                        <i class="fas fa-boxes"></i>
                        <span>{{ config('app.name', 'النظام المحاسبي الأول') }}</span>
                    </a>
                    <h1 class="banner-title">إدارة أعمالك المالية والمبيعات بدقة وسهولة متناهية</h1>
                    <p class="banner-text">الخيار الاحترافي الأول للمحلات والمؤسسات لإدارة نقاط البيع، المخازن، والتقارير المالية بدقة واحترافية.</p>
                    
                    <div class="banner-bullets">
                        <div class="bullet-item">
                            <i class="fas fa-check-circle"></i>
                            <span>شاشة كاشير (POS) تفاعلية وسريعة تدعم الدفع المختلط.</span>
                        </div>
                        <div class="bullet-item">
                            <i class="fas fa-check-circle"></i>
                            <span>دليل الحسابات وقيود اليومية الآلية والمستندات.</span>
                        </div>
                        <div class="bullet-item">
                            <i class="fas fa-check-circle"></i>
                            <span>إدارة الورديات وإغلاق الصناديق مع تتبع العجز والزيادة.</span>
                        </div>
                        <div class="bullet-item">
                            <i class="fas fa-check-circle"></i>
                            <span>تقارير مالية تفصيلية، موازين المراجعة والأرباح.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جانب نموذج الإدخال -->
            <div class="login-form-side">
                <div class="form-container">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

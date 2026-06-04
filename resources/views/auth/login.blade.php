<x-guest-layout>
    <div class="form-header">
        <h2>تسجيل الدخول</h2>
        <p>مرحباً بك مجدداً! يرجى إدخال بياناتك للدخول إلى النظام</p>
    </div>

    <!-- حالة الجلسة وتنبيهات الأخطاء -->
    @if ($errors->any())
        <div class="alert-container">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="status-container">
            <i class="fas fa-check-circle"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- البريد الإلكتروني -->
        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <div class="input-wrapper">
                <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@company.com">
                <i class="fas fa-envelope"></i>
            </div>
        </div>

        <!-- كلمة المرور -->
        <div class="form-group" style="margin-top: 20px;">
            <label for="password">كلمة المرور</label>
            <div class="input-wrapper">
                <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                <i class="fas fa-lock"></i>
            </div>
        </div>

        <!-- تذكرني ونسيان كلمة المرور -->
        <div class="form-options" style="margin-top: 20px;">
            <label for="remember_me" class="remember-me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>تذكرني على هذا الجهاز</span>
            </label>

            @if (Route::has('password.request'))
                <a class="forgot-password-link" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif
        </div>

        <!-- أزرار الدخول والعودة -->
        <div style="margin-top: 30px;">
            <button type="submit" class="btn-login-submit">
                دخول إلى لوحة التحكم <i class="fas fa-sign-in-alt" style="margin-right: 6px;"></i>
            </button>
        </div>
    </form>

    <div style="text-align: center; margin-top: 10px;">
        <a href="{{ url('/') }}" class="btn-back-home">
            <i class="fas fa-arrow-right"></i> الرجوع للرئيسية
        </a>
    </div>
</x-guest-layout>

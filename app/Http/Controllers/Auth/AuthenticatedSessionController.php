<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id() ?? \App\Models\User::first()->GUID;
        $activeShift = \App\Models\Shift::where('GUSER', $userId)->where('CP', false)->first();

        if ($activeShift) {
            return redirect()->route('shifts.index')->with('error', 'تنبيه: لا يمكنك تسجيل الخروج بينما الوردية ما زالت مفتوحة! يرجى إغلاق الوردية الحالية وجرد الصندوق أولاً.');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

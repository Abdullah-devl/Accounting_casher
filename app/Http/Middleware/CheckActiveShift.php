<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;

class CheckActiveShift
{
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id() ?? 1;
        $hasActiveShift = Shift::where('user_id', $userId)->where('is_closed', false)->exists();

        if (!$hasActiveShift) {
            // توجيه الكاشير لشاشة الورديات مع رسالة تحذيرية
            return redirect()->route('shifts.index')->with('error', 'يجب فتح وردية واستلام العهدة أولاً قبل الدخول لشاشة الكاشير.');
        }

        return $next($request);
    }
}
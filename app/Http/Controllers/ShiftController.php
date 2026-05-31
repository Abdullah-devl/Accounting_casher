<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\Invoice;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    // 1. عرض شاشة إدارة الورديات
    public function index()
    {
        $userId = Auth::id() ?? User::first()->GUID;

        // جلب الوردية النشطة (المفتوحة) للمستخدم الحالي إن وجدت
        $activeShift = Shift::where('GUSER', $userId)
                            ->where('CP', false)
                            ->first();

        // جلب سجل الورديات السابقة (المغلقة)
        $pastShifts = Shift::where('GUSER', $userId)
                           ->where('CP', true)
                           ->orderBy('closed_at', 'desc')
                           ->get();

        // إذا كان هناك وردية نشطة، نحسب مبيعاتها حتى اللحظة عبر جدول الربط TB_BACH2
        $currentSales = 0;
        if ($activeShift) {
            $currentSales = Invoice::join('TB_BACH2', 'BILL1.GUID', '=', 'TB_BACH2.GUID_BILL')
                                   ->where('TB_BACH2.PARENTGUID', $activeShift->GUID)
                                   ->where('BILL1.TYPE_PAY', 0) // المبيعات النقدية فقط
                                   ->where('BILL1.WAIT', false)
                                   ->sum('BILL1.TOT_FINLY');
        }

        return view('shifts.index', compact('activeShift', 'pastShifts', 'currentSales'));
    }

    // 2. فتح وردية جديدة (Create)
    public function store(Request $request)
    {
        $userId = Auth::id() ?? User::first()->GUID;

        // منع فتح وردية إذا كان لديه وردية مفتوحة بالفعل
        $hasActive = Shift::where('GUSER', $userId)->where('CP', false)->exists();
        if ($hasActive) {
            return redirect()->back()->with('error', 'مرفوض: لديك وردية مفتوحة بالفعل.');
        }

        $nextNumber = Shift::max('NUMBER') + 1;
        if (!$nextNumber) {
            $nextNumber = 1;
        }

        Shift::create([
            'GUID' => (string) Str::uuid(),
            'NUMBER' => $nextNumber,
            'GUSER' => $userId,
            'DATE' => now(),
            'opening_cash' => $request->opening_cash ?? 0.0,
            'CP' => false,
        ]);

        return redirect()->back()->with('success', 'تم استلام العهدة وفتح الوردية بنجاح. يمكنك الآن استخدام الكاشير.');
    }

    // 3. إغلاق الوردية وجرد الصندوق (Update/Close)
    public function close(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        if ($shift->CP) {
            return redirect()->back()->with('error', 'هذه الوردية مغلقة مسبقاً.');
        }

        // حساب إجمالي المبيعات "النقدية" التي تمت خلال هذه الوردية عبر جدول الربط
        $cashSales = Invoice::join('TB_BACH2', 'BILL1.GUID', '=', 'TB_BACH2.GUID_BILL')
                            ->where('TB_BACH2.PARENTGUID', $shift->GUID)
                            ->where('BILL1.TYPE_PAY', 0) // 0 تعني نقدي
                            ->where('BILL1.WAIT', false)
                            ->sum('BILL1.TOT_FINLY');

        // حساب المبلغ المتوقع (العهدة الافتتاحية + المبيعات النقدية)
        $expectedCash = $shift->opening_cash + $cashSales;

        // إغلاق الوردية وحفظ بيانات الجرد
        $shift->update([
            'closed_at' => now(),
            'CP' => true, // مغلقة
            'expected_cash' => $expectedCash,
            'actual_cash' => $request->actual_cash, // المبلغ الذي عده الكاشير
        ]);

        $difference = $request->actual_cash - $expectedCash;
        $msg = 'تم إغلاق الوردية. ';
        $msg .= $difference < 0 ? 'يوجد عجز بقيمة: ' . abs($difference) : 'يوجد زيادة/تطابق بقيمة: ' . $difference;

        return redirect()->back()->with('success', $msg);
    }
}
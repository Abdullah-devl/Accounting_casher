<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\JournalDetail;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    // 1. عرض شجرة الحسابات (Read)
    public function index()
    {
        // جلب الحسابات الجذرية (التي ليس لها أب) مع جميع تفرعاتها
        $accounts = Account::whereNull('PARENT_GUID')->with('children')->get();
        // جلب كل الحسابات لاستخدامها في القوائم المنسدلة
        $allAccounts = Account::all(); 
        
        return view('accounts.index', compact('accounts', 'allAccounts'));
    }

    // 2. إضافة حساب جديد (Create)
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:ACCOUNT,CODE',
            'name' => 'required|string',
            'account_type' => 'required|integer' // 0 = رئيسي، 1 = فرعي
        ]);

        Account::create([
            'GUID' => (string) Str::uuid(),
            'CODE' => $request->code,
            'NAME' => $request->name,
            'PARENT_GUID' => $request->parent_id, // قد يكون null إذا كان حساباً جذرياً
            'TYPE' => $request->account_type,
            'DEBIT' => $request->opening_debit ?? 0.0,
            'CREDIT' => $request->opening_credit ?? 0.0,
            'FREEZ' => false
        ]);

        return redirect()->back()->with('success', 'تم إضافة الحساب بنجاح.');
    }

    // 3. تعديل وتجميد الحساب (Update)
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        
        $account->update([
            'NAME' => $request->name,
            'FREEZ' => $request->has('is_frozen')
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الحساب.');
    }

    // 4. حذف الحساب بشروط (Delete)
    public function destroy($id)
    {
        $account = Account::findOrFail($id);

        // الشرط الأول: هل الحساب له أبناء؟
        if ($account->children()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف حساب رئيسي يحتوي على حسابات فرعية متفرعة منه.');
        }

        // الشرط الثاني: هل الحساب يمتلك حركات مالية في جدول تفاصيل القيود (DAY2)؟
        $hasTransactions = JournalDetail::where('ACCOUNT_GUID', $id)->exists();
        if ($hasTransactions) {
            return redirect()->back()->with('error', 'مرفوض: لا يمكن حذف حساب تمت عليه حركات مالية مسجلة.');
        }

        $account->delete();
        return redirect()->back()->with('success', 'تم حذف الحساب بنجاح.');
    }
}
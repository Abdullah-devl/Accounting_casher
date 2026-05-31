<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashReceipt;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashReceiptController extends Controller
{
    // 1. عرض قائمة السندات السابقة
    public function index()
    {
        $receipts = CashReceipt::orderBy('DATE', 'desc')->paginate(20);
        return view('cash_receipts.index', compact('receipts'));
    }

    // 2. واجهة إضافة سند جديد
    public function create()
    {
        // جلب الحسابات الفرعية
        $accounts = Account::where('TYPE', 1)->where('FREEZ', false)->get();
        return view('cash_receipts.create', compact('accounts'));
    }

    // 3. حفظ السند وتوليد القيد الآلي
    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|numeric',
            'receipt_type' => 'required|in:1,2',
            'amount' => 'required|numeric|min:0.1',
            'cash_account_id' => 'required',
            'target_account_id' => 'required|different:cash_account_id',
        ]);

        DB::beginTransaction();
        try {
            $receiptGuid = (string) Str::uuid();
            $defaultCurrency = Currency::first();
            $currencyGuid = $defaultCurrency ? $defaultCurrency->GUID : null;

            // أ. حفظ السند في جدول CASH_DAY
            $receipt = CashReceipt::create([
                'GUID' => $receiptGuid,
                'NUMBER' => $request->receipt_number,
                'TYPE_NUMBER' => $request->receipt_type,
                'DATE' => $request->receipt_date ?? now(),
                'VAL_VOCHERS' => $request->amount,
                'NOTE' => $request->note,
                'GUID_ACCOUNT' => $request->cash_account_id,
                'GUID_CUSTOMER' => $request->target_account_id,
                'GUID_CURRENCY' => $currencyGuid,
                'CURRENCY_VAL' => 1.0,
                'GUID_CURRENCY1' => $currencyGuid,
                'CURRENCY_VAL1' => 1.0,
                'VAL_VOCHERS2' => 0.0,
            ]);

            // ب. إنشاء رأس القيد في جدول DAY1
            $typeName = $receipt->TYPE_NUMBER == 1 ? 'سند قبض' : 'سند صرف';
            $journalGuid = (string) Str::uuid();
            $journal = JournalEntry::create([
                'GUID' => $journalGuid,
                'TYPE_NUMBER' => $receipt->TYPE_NUMBER == 1 ? 12 : 13, // 12 for receipt voucher, 13 for payment voucher
                'NUMBER' => $receipt->NUMBER,
                'DATE' => $receipt->DATE,
                'NOTE' => "قيد آلي لـ $typeName رقم " . $receipt->NUMBER,
                'GUID_CURRENCY' => $currencyGuid,
                'CURRENCY_VAL' => 1.0,
            ]);

            // ج. إنشاء أطراف القيد (DAY2) بناءً على نوع السند
            if ($receipt->TYPE_NUMBER == 1) {
                // *** حالة سند القبض ***
                // الصندوق استلم أموالاً (مدين) والعميل دفع (دائن)
                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $receipt->GUID_ACCOUNT,
                    'DEBIT' => $receipt->VAL_VOCHERS,
                    'CREDIT' => 0.0,
                    'NOTE' => "قبض نقدي: " . $receipt->NOTE,
                    'GUID_CURRENCY' => $currencyGuid,
                    'CURRENCY_VAL' => 1.0,
                    'VAL_LOCALY' => $receipt->VAL_VOCHERS,
                ]);

                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $receipt->GUID_CUSTOMER,
                    'DEBIT' => 0.0,
                    'CREDIT' => $receipt->VAL_VOCHERS,
                    'NOTE' => "دفعة نقدية: " . $receipt->NOTE,
                    'GUID_CURRENCY' => $currencyGuid,
                    'CURRENCY_VAL' => 1.0,
                    'VAL_LOCALY' => $receipt->VAL_VOCHERS,
                ]);
            } else {
                // *** حالة سند الصرف ***
                // الصندوق دفع أموالاً (دائن) والمورد/المصروف استلم (مدين)
                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $receipt->GUID_CUSTOMER,
                    'DEBIT' => $receipt->VAL_VOCHERS,
                    'CREDIT' => 0.0,
                    'NOTE' => "صرف نقدي: " . $receipt->NOTE,
                    'GUID_CURRENCY' => $currencyGuid,
                    'CURRENCY_VAL' => 1.0,
                    'VAL_LOCALY' => $receipt->VAL_VOCHERS,
                ]);

                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $receipt->GUID_ACCOUNT,
                    'DEBIT' => 0.0,
                    'CREDIT' => $receipt->VAL_VOCHERS,
                    'NOTE' => "دفعة من الصندوق: " . $receipt->NOTE,
                    'GUID_CURRENCY' => $currencyGuid,
                    'CURRENCY_VAL' => 1.0,
                    'VAL_LOCALY' => $receipt->VAL_VOCHERS,
                ]);
            }

            DB::commit();
            return redirect()->route('cash_receipts.create')->with('success', "تم حفظ الـ $typeName وتوليد القيد المحاسبي بنجاح.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ السند: ' . $e->getMessage());
        }
    }
}
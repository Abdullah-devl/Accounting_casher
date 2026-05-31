<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Account;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalEntryController extends Controller
{
    // 1. عرض القيود السابقة (Read)
    public function index()
    {
        $entries = JournalEntry::orderBy('DATE', 'desc')->paginate(20);
        return view('journal_entries.index', compact('entries'));
    }

    // 2. واجهة الإضافة (Create)
    public function create()
    {
        // الحسابات الفرعية فقط هي التي تقبل القيود (TYPE = 1)
        $accounts = Account::where('TYPE', 1)->where('FREEZ', false)->get();
        return view('journal_entries.create', compact('accounts'));
    }

    // 3. حفظ القيد اليدوي مع الفحص المحاسبي الدقيق (Store)
    public function store(Request $request)
    {
        $request->validate([
            'entry_number' => 'required|numeric',
            'entry_date' => 'required|date',
            'items' => 'required|array|min:2', // يجب أن يحتوي القيد على سطرين على الأقل
        ]);

        // الفحص المحاسبي في الـ Backend
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($request->items as $item) {
            $totalDebit += (float) ($item['debit'] ?? 0);
            $totalCredit += (float) ($item['credit'] ?? 0);
        }

        // يجب أن يتساوى الطرفان، ويجب ألا يكون المجموع صفراً
        if (round($totalDebit, 2) !== round($totalCredit, 2) || $totalDebit == 0) {
            return response()->json(['success' => false, 'message' => 'القيد غير متزن! إجمالي المدين لا يساوي إجمالي الدائن.']);
        }

        DB::beginTransaction();
        try {
            $journalGuid = (string) Str::uuid();
            $defaultCurrency = Currency::first();
            $currencyGuid = $defaultCurrency ? $defaultCurrency->GUID : null;

            // حفظ رأس القيد في جدول DAY1
            $journal = JournalEntry::create([
                'GUID' => $journalGuid,
                'TYPE_NUMBER' => 14, // 14 للقيود اليدوية العامة
                'NUMBER' => $request->entry_number,
                'DATE' => $request->entry_date,
                'NOTE' => $request->note ?? 'قيد تسوية يدوي',
                'GUID_CURRENCY' => $currencyGuid,
                'CURRENCY_VAL' => 1.0,
            ]);

            // حفظ أطراف القيد في جدول DAY2
            foreach ($request->items as $itemData) {
                $debit = (float) ($itemData['debit'] ?? 0);
                $credit = (float) ($itemData['credit'] ?? 0);
                
                if ($debit > 0 || $credit > 0) {
                    $valLocaly = $debit > 0 ? $debit : $credit;

                    JournalDetail::create([
                        'PARENT_GUID' => $journalGuid,
                        'ACCOUNT_GUID' => $itemData['account_id'],
                        'DEBIT' => $debit,
                        'CREDIT' => $credit,
                        'NOTE' => $itemData['note'] ?? '',
                        'GUID_CURRENCY' => $currencyGuid,
                        'CURRENCY_VAL' => 1.0,
                        'VAL_LOCALY' => $valLocaly,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم حفظ القيد المحاسبي المتزن بنجاح.', 'redirect' => route('journal_entries.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
}
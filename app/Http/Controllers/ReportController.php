<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Item;
use App\Models\JournalDetail;
use App\Models\InvoiceDetail;

class ReportController extends Controller
{
    // 1. عرض لوحة تحكم التقارير (الفلاتر)
    public function index()
    {
        $accounts = Account::where('TYPE', 1)->get(); // الحسابات الفرعية فقط (TYPE = 1)
        $items = Item::where('FREEZ', false)->get(); // الأصناف النشطة (FREEZ = false)

        return view('reports.index', compact('accounts', 'items'));
    }

    // 2. معالجة وتوليد التقارير
    public function generate(Request $request)
    {
        $reportType = $request->report_type;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // ==========================================
        // أ. تقرير كشف حساب (Account Statement)
        // ==========================================
        if ($reportType == 'account_statement') {
            $account = Account::findOrFail($request->account_id);
            
            // 1. حساب الرصيد السابق (التراكمي قبل تاريخ البداية)
            $previousDebit = JournalDetail::where('ACCOUNT_GUID', $account->GUID)
                ->whereHas('entry', function($q) use ($fromDate) {
                    $q->whereDate('DATE', '<', $fromDate);
                })->sum('DEBIT');

            $previousCredit = JournalDetail::where('ACCOUNT_GUID', $account->GUID)
                ->whereHas('entry', function($q) use ($fromDate) {
                    $q->whereDate('DATE', '<', $fromDate);
                })->sum('CREDIT');

            $previousBalance = $previousDebit - $previousCredit; // الموجب = مدين، السالب = دائن

            // 2. جلب الحركات المالية خلال الفترة المحددة
            $transactions = JournalDetail::where('ACCOUNT_GUID', $account->GUID)
                ->with('entry')
                ->whereHas('entry', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('DATE', [$fromDate, $toDate]);
                })
                ->get()
                ->sortBy(function($detail) {
                    return $detail->entry->DATE; // ترتيب زمني
                });

            return view('reports.print_account_statement', compact('account', 'fromDate', 'toDate', 'previousBalance', 'transactions'));
        }

        // ==========================================
        // ب. تقرير ميزان المراجعة (Trial Balance)
        // ==========================================
        elseif ($reportType == 'trial_balance') {
            // جلب مجاميع المدين والدائن لكل حساب خلال الفترة
            $accounts = Account::where('TYPE', 1)
                ->withSum(['journalDetails as total_debit' => function($q) use ($fromDate, $toDate) {
                    $q->whereHas('entry', function($query) use ($fromDate, $toDate) {
                        $query->whereBetween('DATE', [$fromDate, $toDate]);
                    });
                }], 'DEBIT')
                ->withSum(['journalDetails as total_credit' => function($q) use ($fromDate, $toDate) {
                    $q->whereHas('entry', function($query) use ($fromDate, $toDate) {
                        $query->whereBetween('DATE', [$fromDate, $toDate]);
                    });
                }], 'CREDIT')
                ->get();

            return view('reports.print_trial_balance', compact('accounts', 'fromDate', 'toDate'));
        }

        // ==========================================
        // ج. تقرير حركة مادة (Item Movement)
        // ==========================================
        elseif ($reportType == 'item_movement') {
            $item = Item::findOrFail($request->item_id);

            // جلب حركات الصنف من الفواتير (بيع وشراء)
            $movements = InvoiceDetail::where('GUID_ITEM', $item->GUID)
                ->with('invoice')
                ->whereHas('invoice', function($q) use ($fromDate, $toDate) {
                    $q->whereBetween('DATE', [$fromDate, $toDate]);
                })
                ->get()
                ->sortBy(function($detail) {
                    return $detail->invoice->DATE;
                });

            return view('reports.print_item_movement', compact('item', 'fromDate', 'toDate', 'movements'));
        }
    }
}
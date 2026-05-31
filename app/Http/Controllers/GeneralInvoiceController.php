<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Account;
use App\Models\TypeBill;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GeneralInvoiceController extends Controller
{
    // 1. عرض قائمة الفواتير السابقة (Read)
    public function index(Request $request)
    {
        $query = Invoice::with(['account', 'typeBill'])->where('WAIT', false);

        // نظام التصفية والبحث
        if ($request->has('invoice_number') && $request->invoice_number != '') {
            $query->where('NUMBER', $request->invoice_number);
        }
        if ($request->has('type_id') && $request->type_id != '') {
            $query->where('GUID_BIIL', $request->type_id);
        }

        $invoices = $query->orderBy('DATE', 'desc')->paginate(20);
        $types = TypeBill::all();

        return view('general_invoices.index', compact('invoices', 'types'));
    }

    // 2. شاشة إضافة فاتورة جديدة (Create)
    public function create()
    {
        // جلب الحسابات الفرعية وأنواع الفواتير المعرفة
        $accounts = Account::where('TYPE', 1)->get();
        $types = TypeBill::all();
        $items = Item::where('FREEZ', false)->get(); // للأوتوكومبليت (Autocomplete)

        return view('general_invoices.create', compact('accounts', 'types', 'items'));
    }

    // 3. حفظ الفاتورة وتوليد القيد المحاسبي (Store)
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $typeBill = TypeBill::findOrFail($request->type_id);
            $invoiceGuid = (string) Str::uuid();

            // حفظ رأس الفاتورة في جدول BILL1
            $invoice = Invoice::create([
                'GUID' => $invoiceGuid,
                'NUMBER' => $request->invoice_number,
                'GUID_BIIL' => $typeBill->GUID,
                'TYPE_NUMBER' => $typeBill->CODE, // رمز نوع الفاتورة
                'DATE' => $request->invoice_date ?? now(),
                'TYPE_PAY' => $request->payment_method, // 0 نقدي، 1 آجل، 2 شبكة
                'ACCOUNT' => $request->account_id, // حساب العميل أو المورد
                'NOTE' => $request->note,
                'TOT_DIS' => $request->total_discount ?? 0,
                'TOT_VAT' => $request->total_vat ?? 0,
                'DIS' => 0.0,
                'TOT_FINLY' => $request->net_amount,
                'POS' => 0, // فاتورة إدارة وليست كاشير
                'WAIT' => false,
                'TOT_PAY' => $request->payment_method == 0 ? $request->net_amount : 0.0,
                'TOT_LEFT' => $request->payment_method == 1 ? $request->net_amount : 0.0,
                'STORE_GUID' => $typeBill->GUID_STORE,
                'GUID_JOB' => $typeBill->GUID_JOB,
                'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
            ]);

            // حفظ تفاصيل الفاتورة في جدول BILL2
            foreach ($request->items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $qty = $itemData['qty'];
                $price = $itemData['price'];
                $discount = $itemData['discount'] ?? 0;
                $vat = $itemData['vat'] ?? 0;
                $totals = $qty * $price;
                $totalsFinally = $totals - $discount + $vat;

                InvoiceDetail::create([
                    'PARENT_GUID' => $invoiceGuid,
                    'GUID_ITEM' => $itemData['item_id'],
                    'QTY' => $qty,
                    'PRICE' => $price,
                    'UNITE' => $itemData['unit'] ?? $item->UNITE1,
                    'QTY_UNITE' => 1.0,
                    'COST' => $item->COST1 ?? 0.0,
                    'EARN' => ($price - ($item->COST1 ?? 0.0)) * $qty,
                    'DIS' => $discount,
                    'VAT' => $vat,
                    'TOTALS' => $totals,
                    'TOTALS_FINLY' => $totalsFinally,
                ]);
            }

            // توليد القيد المحاسبي آلياً في DAY1
            $journalGuid = (string) Str::uuid();
            $journal = JournalEntry::create([
                'GUID' => $journalGuid,
                'TYPE_NUMBER' => $typeBill->CODE,
                'NUMBER' => $invoice->NUMBER,
                'DATE' => $invoice->DATE,
                'NOTE' => "ترحيل الفاتورة رقم " . $invoice->NUMBER,
                'GUID_JOB' => $typeBill->GUID_JOB,
                'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
            ]);

            // توجيه الأطراف المالية في DAY2
            // 1. الطرف المدين: الصندوق (للشبكة/النقدي) أو حساب العميل (للآجل)
            $debtorAccount = ($request->payment_method == 0 || $request->payment_method == 2) 
                ? $typeBill->cash_day 
                : $request->account_id;

            JournalDetail::create([
                'PARENT_GUID' => $journalGuid,
                'ACCOUNT_GUID' => $debtorAccount,
                'DEBIT' => $invoice->TOT_FINLY,
                'CREDIT' => 0.0,
                'NOTE' => "مبيعات فاتورة رقم " . $invoice->NUMBER,
                'GUID_JOB' => $typeBill->GUID_JOB,
                'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                'VAL_LOCALY' => $invoice->TOT_FINLY * ($typeBill->VAL_CURRENCY ?? 1.0),
            ]);

            // 2. الطرف الدائن: حساب المبيعات (بدون ضريبة)
            $salesRevenue = $invoice->TOT_FINLY - $invoice->TOT_VAT;
            JournalDetail::create([
                'PARENT_GUID' => $journalGuid,
                'ACCOUNT_GUID' => $typeBill->day_item, // حساب المبيعات
                'DEBIT' => 0.0,
                'CREDIT' => $salesRevenue,
                'NOTE' => "مبيعات فاتورة رقم " . $invoice->NUMBER,
                'GUID_JOB' => $typeBill->GUID_JOB,
                'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                'VAL_LOCALY' => $salesRevenue * ($typeBill->VAL_CURRENCY ?? 1.0),
            ]);

            // 3. الطرف الدائن الثاني: حساب الضريبة (إن وجدت)
            if ($invoice->TOT_VAT > 0) {
                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $typeBill->cash_vat, // حساب الضريبة
                    'DEBIT' => 0.0,
                    'CREDIT' => $invoice->TOT_VAT,
                    'NOTE' => "ضريبة فاتورة رقم " . $invoice->NUMBER,
                    'GUID_JOB' => $typeBill->GUID_JOB,
                    'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                    'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                    'VAL_LOCALY' => $invoice->TOT_VAT * ($typeBill->VAL_CURRENCY ?? 1.0),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم حفظ الفاتورة وترحيل القيد بنجاح.', 'redirect' => route('general_invoices.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()]);
        }
    }

    // 4. حذف الفاتورة (Delete)
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        // بفضل علاقة cascade onDelete في المهاجرات، سيقوم بحذف الفاتورة والتفاصيل والقيود المرتبطة بها تلقائياً
        $invoice->delete();

        return redirect()->back()->with('success', 'تم حذف الفاتورة والقيود المرتبطة بها بنجاح.');
    }
}
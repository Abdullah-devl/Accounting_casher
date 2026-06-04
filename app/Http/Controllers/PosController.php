<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Shift;
use App\Models\TypeBill;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    // 1. عرض شاشة الكاشير
    public function index()
    {
        $userId = Auth::id() ?? \App\Models\User::first()->GUID;
        $activeShift = Shift::where('GUSER', $userId)->where('CP', false)->first();

        if (!$activeShift) {
            return redirect()->route('shifts.index')->with('error', 'يجب فتح وردية جديدة أولاً لبدء البيع في شاشة الكاشير!');
        }

        // جلب التصنيفات مع أصنافها لعرضها كأزرار سريعة
        $categories = Category::with(['items' => function($query) {
            $query->where('FREEZ', false);
        }])->get();

        // جلب الفواتير المعلقة الخاصة بالكاشير الحالي
        $heldInvoices = Invoice::where('WAIT', true)
            ->where('TYPE_NUMBER', 1)
            ->get();

        return view('pos.index', compact('categories', 'heldInvoices'));
    }

    // 2. البحث عن صنف عبر الباركود
    public function scanBarcode($barcode)
    {
        $item = Item::where('barcode1', $barcode)
                    ->orWhere('barcode2', $barcode)
                    ->orWhere('barcode3', $barcode)
                    ->orWhere('NUMBER', $barcode)
                    ->orWhere('GUID', $barcode)
                    ->first();

        if ($item && !$item->FREEZ) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return response()->json(['success' => false, 'message' => 'الصنف غير موجود أو مجمد.']);
    }

    // 3. حفظ الفاتورة (نقدي، شبكة، أو تعليق)
    public function store(Request $request)
    {
        $userId = Auth::id() ?? User::first()->GUID; // جلب المعرف GUID للمستخدم الحالي
        $activeShift = Shift::where('GUSER', $userId)->where('CP', false)->first();

        if (!$activeShift) {
            return response()->json(['success' => false, 'message' => 'لا توجد وردية مفتوحة!'], 403);
        }

        DB::beginTransaction();
        try {
            $invoiceGuid = (string) Str::uuid();
            $nextInvoiceNumber = Invoice::max('NUMBER') + 1;
            if (!$nextInvoiceNumber || $nextInvoiceNumber < 1000) {
                $nextInvoiceNumber = 1000;
            }

            // جلب تهيئة فاتورة مبيعات نقاط البيع الافتراضية
            $typeBill = TypeBill::where('TYPE', true)->first();

            // حفظ رأس الفاتورة في جدول BILL1
            $invoice = Invoice::create([
                'GUID' => $invoiceGuid,
                'NUMBER' => $nextInvoiceNumber,
                'TYPE_NUMBER' => 1, // مبيعات
                'DATE' => now(),
                'TYPE_PAY' => $request->payment_method, // 0 نقدي، 1 شبكة، 2 مختلط
                'TOT_DIS' => $request->total_discount ?? 0,
                'TOT_VAT' => $request->total_vat,
                'DIS' => 0.0,
                'TOT_FINLY' => $request->net_amount,
                'POS' => 1, // POS
                'WAIT' => $request->is_waiting == 'true',
                'TOT_PAY' => $request->is_waiting == 'true' ? 0.0 : $request->net_amount,
                'TOT_LEFT' => 0.0,
                'GUID_BIIL' => $typeBill ? $typeBill->GUID : null,
                'STORE_GUID' => $typeBill ? $typeBill->GUID_STORE : null,
                'GUID_JOB' => $typeBill ? $typeBill->GUID_JOB : null,
                'GUID_CURRENCY' => $typeBill ? $typeBill->GUID_CURRENCY : null,
                'CURRENCY_VAL' => $typeBill ? ($typeBill->VAL_CURRENCY ?? 1.0) : 1.0,
            ]);

            // حفظ تفاصيل الفاتورة في جدول BILL2
            foreach ($request->items as $itemData) {
                $itemId = $itemData['GUID'] ?? $itemData['id'];
                $item = Item::findOrFail($itemId);
                $qty = $itemData['qty'];
                $price = $itemData['price'];
                $totals = $qty * $price;
                $vat = $itemData['vat'] ?? 0.0;

                InvoiceDetail::create([
                    'PARENT_GUID' => $invoiceGuid,
                    'GUID_ITEM' => $item->GUID,
                    'QTY' => $qty,
                    'PRICE' => $price,
                    'UNITE' => $item->UNITE1,
                    'QTY_UNITE' => 1.0,
                    'COST' => $item->COST1 ?? 0.0,
                    'EARN' => ($price - ($item->COST1 ?? 0.0)) * $qty,
                    'DIS' => 0.0,
                    'VAT' => $vat,
                    'TOTALS' => $totals,
                    'TOTALS_FINLY' => $totals + $vat,
                ]);
            }

            // إذا لم تكن الفاتورة معلقة، نربطها بالوردية ونقوم بالترحيل المالي
            if ($request->is_waiting != 'true') {
                // ربط الفاتورة بالوردية في جدول TB_BACH2
                DB::table('TB_BACH2')->insert([
                    'PARENTGUID' => $activeShift->GUID,
                    'GUID_BILL' => $invoiceGuid,
                ]);

                if ($typeBill) {
                    // توليد قيد اليومية في DAY1
                    $journalGuid = (string) Str::uuid();
                    JournalEntry::create([
                        'GUID' => $journalGuid,
                        'TYPE_NUMBER' => 1,
                        'NUMBER' => $invoice->NUMBER,
                        'DATE' => $invoice->DATE,
                        'NOTE' => "ترحيل مبيعات POS فاتورة رقم " . $invoice->NUMBER,
                        'GUID_JOB' => $typeBill->GUID_JOB,
                        'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                        'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                    ]);

                    // 1. المدين: الصندوق الخاص بنقاط البيع
                    JournalDetail::create([
                        'PARENT_GUID' => $journalGuid,
                        'ACCOUNT_GUID' => $typeBill->cash_day,
                        'DEBIT' => $invoice->TOT_FINLY,
                        'CREDIT' => 0.0,
                        'NOTE' => "مبيعات فاتورة رقم " . $invoice->NUMBER,
                        'GUID_JOB' => $typeBill->GUID_JOB,
                        'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                        'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                        'VAL_LOCALY' => $invoice->TOT_FINLY * ($typeBill->VAL_CURRENCY ?? 1.0),
                    ]);

                    // 2. الدائن: حساب المبيعات
                    $salesRevenue = $invoice->TOT_FINLY - $invoice->TOT_VAT;
                    JournalDetail::create([
                        'PARENT_GUID' => $journalGuid,
                        'ACCOUNT_GUID' => $typeBill->day_item,
                        'DEBIT' => 0.0,
                        'CREDIT' => $salesRevenue,
                        'NOTE' => "مبيعات فاتورة رقم " . $invoice->NUMBER,
                        'GUID_JOB' => $typeBill->GUID_JOB,
                        'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                        'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                        'VAL_LOCALY' => $salesRevenue * ($typeBill->VAL_CURRENCY ?? 1.0),
                    ]);

                    // 3. الدائن: حساب الضريبة
                    if ($invoice->TOT_VAT > 0) {
                        JournalDetail::create([
                            'PARENT_GUID' => $journalGuid,
                            'ACCOUNT_GUID' => $typeBill->cash_vat,
                            'DEBIT' => 0.0,
                            'CREDIT' => $invoice->TOT_VAT,
                            'NOTE' => "ضريبة فاتورة رقم " . $invoice->NUMBER,
                            'GUID_JOB' => $typeBill->GUID_JOB,
                            'GUID_CURRENCY' => $typeBill->GUID_CURRENCY,
                            'CURRENCY_VAL' => $typeBill->VAL_CURRENCY ?? 1.0,
                            'VAL_LOCALY' => $invoice->TOT_VAT * ($typeBill->VAL_CURRENCY ?? 1.0),
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => $request->is_waiting == 'true' ? 'تم تعليق الفاتورة.' : 'تم إصدار الفاتورة بنجاح.',
                'invoice_id' => $invoiceGuid
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()]);
        }
    }
}
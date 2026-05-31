<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Item;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    // 1. دالة لعرض شاشة الكاشير (الـ View)
    public function create()
    {
        return view('invoices.create'); 
    }

    // 2. دالة استقبال بيانات النموذج وحفظها
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $invoiceGuid = (string) Str::uuid();
            
            // حفظ رأس الفاتورة في جدول BILL1
            $invoice = Invoice::create([
                'GUID' => $invoiceGuid,
                'NUMBER' => $request->invoice_number,
                'TYPE_NUMBER' => 1, // مبيعات
                'DATE' => now(),
                'TYPE_PAY' => $request->payment_method, // 0 نقدي، 1 آجل، 2 شبكة
                'TOT_DIS' => 0.0,
                'TOT_VAT' => $request->total_vat,
                'TOT_FINLY' => $request->net_amount,
                'POS' => 1, // POS
                'WAIT' => false,
                'TOT_PAY' => $request->payment_method == 0 ? $request->net_amount : 0.0,
                'TOT_LEFT' => $request->payment_method == 1 ? $request->net_amount : 0.0,
            ]);

            foreach ($request->items as $itemData) {
                // البحث عن الصنف في قاعدة البيانات
                $item = Item::where('GUID', $itemData['item_id'])
                            ->orWhere('barcode1', $itemData['item_id'])
                            ->orWhere('barcode2', $itemData['item_id'])
                            ->orWhere('barcode3', $itemData['item_id'])
                            ->first();

                if (!$item) {
                    throw new \Exception("الصنف المختار غير مسجل في النظام: " . $itemData['item_id']);
                }

                $qty = $itemData['qty'];
                $price = $itemData['price'];
                $totals = $qty * $price;
                $vat = $itemData['vat'];

                InvoiceDetail::create([
                    'PARENT_GUID' => $invoiceGuid,
                    'GUID_ITEM' => $item->GUID,
                    'UNITE' => $itemData['unit'],
                    'QTY' => $qty,
                    'PRICE' => $price,
                    'QTY_UNITE' => 1.0,
                    'COST' => $item->COST1 ?? 0,
                    'EARN' => ($price - ($item->COST1 ?? 0)) * $qty,
                    'DIS' => 0.0,
                    'VAT' => $vat,
                    'TOTALS' => $totals,
                    'TOTALS_FINLY' => $totals + $vat,
                ]);
            }

            // توليد القيد المحاسبي
            $journalGuid = (string) Str::uuid();
            $journal = JournalEntry::create([
                'GUID' => $journalGuid,
                'TYPE_NUMBER' => 1,
                'NUMBER' => $invoice->NUMBER,
                'DATE' => now(),
                'NOTE' => "قيد آلي لمبيعات فاتورة رقم " . $invoice->NUMBER,
            ]);

            JournalDetail::create([
                'PARENT_GUID' => $journalGuid,
                'ACCOUNT_GUID' => $request->cash_account_id,
                'DEBIT' => $invoice->TOT_FINLY,
                'CREDIT' => 0.0,
                'NOTE' => "مبيعات نقدية فاتورة " . $invoice->NUMBER,
                'VAL_LOCALY' => $invoice->TOT_FINLY,
            ]);

            $salesRevenue = $invoice->TOT_FINLY - $invoice->TOT_VAT;
            JournalDetail::create([
                'PARENT_GUID' => $journalGuid,
                'ACCOUNT_GUID' => $request->sales_account_id,
                'DEBIT' => 0.0,
                'CREDIT' => $salesRevenue,
                'NOTE' => "إيراد مبيعات فاتورة " . $invoice->NUMBER,
                'VAL_LOCALY' => $salesRevenue,
            ]);

            if ($invoice->TOT_VAT > 0) {
                JournalDetail::create([
                    'PARENT_GUID' => $journalGuid,
                    'ACCOUNT_GUID' => $request->vat_account_id,
                    'DEBIT' => 0.0,
                    'CREDIT' => $invoice->TOT_VAT,
                    'NOTE' => "ضريبة محصلة لفاتورة " . $invoice->NUMBER,
                    'VAL_LOCALY' => $invoice->TOT_VAT,
                ]);
            }
        });

        return redirect()->route('invoices.create')->with('success', 'تم حفظ الفاتورة وتوليد القيد المحاسبي بنجاح!');
    }
}
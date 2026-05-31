<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\InvoiceDetail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    // 1. عرض المواد (Read)
    public function index()
    {
        // جلب الأصناف مع اسم التصنيف الخاص بها
        $items = Item::with('category')->orderBy('NUMBER', 'desc')->get();
        $categories = Category::all(); // للقائمة المنسدلة في نموذج الإضافة
        
        return view('items.index', compact('items', 'categories'));
    }

    // 2. إضافة صنف جديد (Create)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit1' => 'required|string',
            'price1' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('items_images', 'public');
        }

        $nextNumber = Item::max('NUMBER') + 1;

        Item::create([
            'GUID' => (string) Str::uuid(),
            'NUMBER' => $nextNumber,
            'NAME' => $request->name,
            'NOTE' => $request->note,
            'GROUP_GUID' => $request->category_id,
            
            // الوحدة الأولى (الأساسية)
            'barcode1' => $request->barcode1,
            'UNITE1' => $request->unit1,
            'QTY1' => 1.0,
            'COST1' => $request->cost1 ?? 0,
            'PRICE1' => $request->price1 ?? 0,

            // الوحدة الثانية
            'barcode2' => $request->barcode2,
            'UNITE2' => $request->unit2,
            'QTY2' => $request->qty2 ?? 0,
            'COST2' => $request->cost2 ?? 0,
            'PRICE2' => $request->price2 ?? 0,

            // الوحدة الثالثة
            'barcode3' => $request->barcode3,
            'UNITE3' => $request->unit3,
            'QTY3' => $request->qty3 ?? 0,
            'COST3' => $request->cost3 ?? 0,
            'PRICE3' => $request->price3 ?? 0,

            // الإعدادات المتقدمة
            'DEFULT_UNITE' => $request->default_unit ?? 1,
            'DATEP' => $request->production_date,
            'DATEE' => $request->expiry_date,
            'DAY_MEPER' => $request->expiry_warning_days ?? 30,
            'QTY_MEPER' => $request->min_order_qty ?? 0,
            'CT_PER' => $request->has('tax_active'),
            'PER' => $request->tax_percentage ?? 15.0,
            'PATH' => $imagePath,
            'FREEZ' => false
        ]);

        return redirect()->back()->with('success', 'تم إضافة الصنف بنجاح.');
    }

    // 3. تعديل أو تجميد الصنف (Update)
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        $data = [
            'NAME' => $request->name,
            'NOTE' => $request->note,
            'GROUP_GUID' => $request->category_id,
            'barcode1' => $request->barcode1,
            'UNITE1' => $request->unit1,
            'COST1' => $request->cost1 ?? 0,
            'PRICE1' => $request->price1 ?? 0,

            'barcode2' => $request->barcode2,
            'UNITE2' => $request->unit2,
            'QTY2' => $request->qty2 ?? 0,
            'COST2' => $request->cost2 ?? 0,
            'PRICE2' => $request->price2 ?? 0,

            'barcode3' => $request->barcode3,
            'UNITE3' => $request->unit3,
            'QTY3' => $request->qty3 ?? 0,
            'COST3' => $request->cost3 ?? 0,
            'PRICE3' => $request->price3 ?? 0,

            'DEFULT_UNITE' => $request->default_unit ?? 1,
            'DATEP' => $request->production_date,
            'DATEE' => $request->expiry_date,
            'DAY_MEPER' => $request->expiry_warning_days ?? 30,
            'QTY_MEPER' => $request->min_order_qty ?? 0,
            'CT_PER' => $request->has('tax_active'),
            'PER' => $request->tax_percentage ?? 15.0,
            'FREEZ' => $request->has('is_frozen')
        ];

        // تحديث الصورة إذا تم رفع صورة جديدة
        if ($request->hasFile('image')) {
            if ($item->PATH) {
                Storage::disk('public')->delete($item->PATH);
            }
            $data['PATH'] = $request->file('image')->store('items_images', 'public');
        }

        $item->update($data);

        return redirect()->back()->with('success', 'تم تحديث بيانات الصنف.');
    }

    // 4. حذف الصنف بشروط (Delete)
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // التحقق من وجود حركات بيع أو شراء على الصنف في جدول تفاصيل الفواتير (BILL2)
        $hasMovements = InvoiceDetail::where('GUID_ITEM', $id)->exists();
        
        if ($hasMovements) {
            return redirect()->back()->with('error', 'مرفوض: لا يمكن حذف صنف له فواتير مسجلة. يمكنك (تجميده) بدلاً من ذلك لإخفائه من شاشة البيع.');
        }

        // حذف الصورة من السيرفر إذا وجدت
        if ($item->PATH) {
            Storage::disk('public')->delete($item->PATH);
        }

        $item->delete();
        return redirect()->back()->with('success', 'تم حذف الصنف من المستودع بنجاح.');
    }
}
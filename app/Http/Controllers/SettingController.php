<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyInfo;
use App\Models\TypeBill;
use App\Models\PosDevice;
use App\Models\Account;
use App\Models\Currency;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    // عرض شاشة الإعدادات الشاملة
    public function index()
    {
        $company = CompanyInfo::first();
        if (!$company) {
            $company = new CompanyInfo();
        }
        $typeBills = TypeBill::all();
        $posDevices = PosDevice::all();
        
        // جلب الحسابات الفرعية فقط لربطها بالفواتير (TYPE = 1)
        $accounts = Account::where('TYPE', 1)->get(); 

        return view('settings.index', compact('company', 'typeBills', 'posDevices', 'accounts'));
    }

    // حفظ بيانات الشركة
    public function updateCompany(Request $request)
    {
        $company = CompanyInfo::first();
        if (!$company) {
            $company = CompanyInfo::create([
                'GUID' => (string) Str::uuid(),
                'NAMEAR' => $request->name_ar,
                'CT' => $request->cr_number,
                'VAT' => $request->vat_number,
            ]);
        } else {
            $company->update([
                'NAMEAR' => $request->name_ar,
                'CT' => $request->cr_number,
                'VAT' => $request->vat_number,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }

    // حفظ نوع فاتورة جديد
    public function storeTypeBill(Request $request)
    {
        $nextNumber = TypeBill::max('NUMBER') + 1;
        if (!$nextNumber) {
            $nextNumber = 1;
        }

        $defaultCurrency = Currency::first();
        $currencyGuid = $defaultCurrency ? $defaultCurrency->GUID : null;

        TypeBill::create([
            'GUID' => (string) Str::uuid(),
            'NAME' => $request->name,
            'CODE' => $request->type_code,
            'NUMBER' => $nextNumber,
            'day_item' => $request->day_item_account,
            'day_disc' => $request->day_disc_account,
            'cash_day' => $request->cash_day_account,
            'cash_vat' => $request->cash_vat_account,
            'vat_activty' => $request->has('vat_active'),
            'TYPE' => $request->has('is_pos'),
            'VAL_CURRENCY' => 1.0,
            'GUID_CURRENCY' => $currencyGuid,
            'val_vat' => 15.0,
        ]);

        return redirect()->back()->with('success', 'تم إضافة نوع الفاتورة بنجاح');
    }

    // حفظ جهاز كاشير جديد (POS Device)
    public function storePosDevice(Request $request)
    {
        $nextNumber = PosDevice::max('NUMBER') + 1;
        if (!$nextNumber) {
            $nextNumber = 1;
        }

        PosDevice::create([
            'GUID' => (string) Str::uuid(),
            'NUMBER' => $nextNumber,
            'NAME' => $request->name,
            'GUID_SALE' => $request->default_sales_type,
            'ACCOUNT_CASH' => $request->cash_account_id,
            'PRINTER' => $request->printer_name,
            'FREEZ' => false,
            'PAS' => false,
        ]);

        return redirect()->back()->with('success', 'تم تعريف جهاز الكاشير بنجاح');
    }
}
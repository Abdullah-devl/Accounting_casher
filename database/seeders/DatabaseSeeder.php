<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Currency;
use App\Models\Store;
use App\Models\Job;
use App\Models\Category;
use App\Models\Account;
use App\Models\TypeBill;
use App\Models\CompanyInfo;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Company Info
        CompanyInfo::create([
            'GUID' => (string) Str::uuid(),
            'NAMEAR' => 'شركة مقاسوفت المحاسبية',
            'NAMEEN' => 'MgaSoft Accounts Co.',
            'FON' => '0112223333',
            'MOBILE' => '0500000000',
            'ADDRES' => 'الرياض، المملكة العربية السعودية',
            'ADDRES2' => 'شارع العليا العام',
            'CT' => '1010101010',
            'VAT' => '300000000000003',
            'MAIL' => 'info@mgasoft.com',
            'WEB' => 'www.mgasoft.com',
            'img' => null,
            'PATH' => null
        ]);

        // 2. Seed Default Currency
        $currencyGuid = (string) Str::uuid();
        Currency::create([
            'GUID' => $currencyGuid,
            'NUMBER' => 1,
            'NAME' => 'ريال سعودي',
            'PART_NAME' => 'هللة',
            'CURRENCY_VAL' => 1.0
        ]);

        // 3. Seed Default Store
        $storeGuid = (string) Str::uuid();
        Store::create([
            'GUID' => $storeGuid,
            'NUMBER' => 1,
            'NAMEAR' => 'المستودع الرئيسي'
        ]);

        // 4. Seed Default Job (Cost Center)
        $jobGuid = (string) Str::uuid();
        Job::create([
            'GUID' => $jobGuid,
            'NUMBER' => 1,
            'NAMEAR' => 'عام'
        ]);

        // 5. Seed Default Item Category
        $categoryGuid = (string) Str::uuid();
        Category::create([
            'GUID' => $categoryGuid,
            'NUMBER' => 1,
            'NAMEAR' => 'عام'
        ]);

        // 6. Seed Basic Chart of Accounts
        // Root accounts (Main: TYPE = 0)
        $assetsGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $assetsGuid,
            'CODE' => '1',
            'NAME' => 'الأصول',
            'PARENT_GUID' => null,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        $liabilitiesGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $liabilitiesGuid,
            'CODE' => '2',
            'NAME' => 'الخصوم',
            'PARENT_GUID' => null,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        $equityGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $equityGuid,
            'CODE' => '3',
            'NAME' => 'حقوق الملكية',
            'PARENT_GUID' => null,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        $revenuesGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $revenuesGuid,
            'CODE' => '4',
            'NAME' => 'الإيرادات',
            'PARENT_GUID' => null,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        $expensesGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $expensesGuid,
            'CODE' => '5',
            'NAME' => 'المصاريف',
            'PARENT_GUID' => null,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        // Sub root accounts under Assets
        $currentAssetsGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $currentAssetsGuid,
            'CODE' => '11',
            'NAME' => 'الأصول المتداولة',
            'PARENT_GUID' => $assetsGuid,
            'END_ACCOUNT' => 0.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 0
        ]);

        // Leaf Accounts (Sub accounts: TYPE = 1)
        // Cash Account
        $cashAccountGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $cashAccountGuid,
            'CODE' => '1101',
            'NAME' => 'صندوق المعرض الرئيسي',
            'PARENT_GUID' => $currentAssetsGuid,
            'END_ACCOUNT' => 1.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 1,
            'DEBIT' => 0.0,
            'CREDIT' => 0.0
        ]);

        // Sales Revenue Account
        $salesAccountGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $salesAccountGuid,
            'CODE' => '4101',
            'NAME' => 'حساب المبيعات',
            'PARENT_GUID' => $revenuesGuid,
            'END_ACCOUNT' => 4.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 1
        ]);

        // Sales Discount Account
        $discountAccountGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $discountAccountGuid,
            'CODE' => '4102',
            'NAME' => 'حساب الخصم الممنوح',
            'PARENT_GUID' => $revenuesGuid,
            'END_ACCOUNT' => 4.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 1
        ]);

        // VAT Account (Liability)
        $vatAccountGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $vatAccountGuid,
            'CODE' => '2105',
            'NAME' => 'حساب ضريبة القيمة المضافة',
            'PARENT_GUID' => $liabilitiesGuid,
            'END_ACCOUNT' => 2.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 1
        ]);

        // Default Cash Customer Account
        $customerAccountGuid = (string) Str::uuid();
        Account::create([
            'GUID' => $customerAccountGuid,
            'CODE' => '1102',
            'NAME' => 'زبون نقدي عام',
            'PARENT_GUID' => $currentAssetsGuid,
            'END_ACCOUNT' => 1.0,
            'GUID_CURRENCY' => $currencyGuid,
            'TYPE' => 1
        ]);

        // 7. Seed TypeBill Configurations (sales and POS)
        TypeBill::create([
            'GUID' => (string) Str::uuid(),
            'CODE' => 1, // Sales Invoice
            'NAME' => 'فاتورة مبيعات',
            'NUMBER' => 1,
            'day_item' => $salesAccountGuid,
            'day_disc' => $discountAccountGuid,
            'cash_day' => $cashAccountGuid,
            'cash_vat' => $vatAccountGuid,
            'GUID_STORE' => $storeGuid,
            'GUID_JOB' => $jobGuid,
            'GUID_CURRENCY' => $currencyGuid,
            'VAL_CURRENCY' => 1.0,
            'PAY' => 0, // Cash
            'vat_activty' => true,
            'val_vat' => 15.0,
            'ROW1_R' => null,
            'ROW2_R' => null,
            'ROWS_C' => 0,
            'SHOW_JOB' => true,
            'SHOW_CURRENCY' => true,
            'SHOW_VAT' => true,
            'SHOW_STORE' => true,
            'SHOW_DISES' => true,
            'ALLOW_DISES' => true,
            'TYPE' => true // POS Active
        ]);

        // 8. Seed Users inside US000
        User::create([
            'GUID' => (string) Str::uuid(),
            'NUMBER' => 1,
            'NAME' => 'المدير العام',
            'USER_NAME' => 'admin@admin.com',
            'PASSWORD' => bcrypt('123456'),
            'USER_LEVEL' => 0, // admin
            'FREEZ' => false
        ]);

        User::create([
            'GUID' => (string) Str::uuid(),
            'NUMBER' => 2,
            'NAME' => 'كاشير 1',
            'USER_NAME' => 'cashier@admin.com',
            'PASSWORD' => bcrypt('123456'),
            'USER_LEVEL' => 1, // cashier
            'FREEZ' => false
        ]);
    }
}

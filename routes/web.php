<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\GeneralInvoiceController;
use App\Http\Controllers\CashReceiptController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    $stats = [
        'items_count' => \App\Models\Item::count(),
        'accounts_count' => \App\Models\Account::count(),
        'invoices_count' => \App\Models\Invoice::count(),
        'shifts_count' => \App\Models\Shift::count(),
    ];
    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Accounts (دليل الحسابات)
    Route::resource('accounts', AccountController::class)->only(['index', 'store', 'update', 'destroy']);

    // Items (بطاقات المواد)
    Route::resource('items', ItemController::class)->only(['index', 'store', 'update', 'destroy']);

    // General Invoices (الفواتير العامة)
    Route::get('/general-invoices', [GeneralInvoiceController::class, 'index'])->name('general_invoices.index');
    Route::get('/general-invoices/create', [GeneralInvoiceController::class, 'create'])->name('general_invoices.create');
    Route::post('/general-invoices/store', [GeneralInvoiceController::class, 'store'])->name('general_invoices.store');
    Route::delete('/general-invoices/{id}', [GeneralInvoiceController::class, 'destroy'])->name('general_invoices.destroy');

    // Cash Receipts (سندات القبض والصرف)
    Route::get('/cash-receipts', [CashReceiptController::class, 'index'])->name('cash_receipts.index');
    Route::get('/cash-receipts/create', [CashReceiptController::class, 'create'])->name('cash_receipts.create');
    Route::post('/cash-receipts/store', [CashReceiptController::class, 'store'])->name('cash_receipts.store');

    // Journal Entries (قيود اليومية)
    Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal_entries.index');
    Route::get('/journal-entries/create', [JournalEntryController::class, 'create'])->name('journal_entries.create');
    Route::post('/journal-entries/store', [JournalEntryController::class, 'store'])->name('journal_entries.store');

    // Reports (التقارير)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Settings (الإعدادات)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.company.update');
    Route::post('/settings/type-bill', [SettingController::class, 'storeTypeBill'])->name('settings.type-bill.store');
    Route::post('/settings/pos-device', [SettingController::class, 'storePosDevice'])->name('settings.pos-device.store');

    // Shifts (الورديات)
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/{id}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Invoices / Simple POS (الفاتورة اليدوية)
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    // POS Screen / Cashier (شاشة الكاشير المتطورة)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/scan/{barcode}', [PosController::class, 'scanBarcode'])->name('pos.scan');
    Route::post('/pos/store', [PosController::class, 'store'])->name('pos.store');
});

require __DIR__.'/auth.php';


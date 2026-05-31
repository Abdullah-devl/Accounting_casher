<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ShiftController;

// مسار مخصص لحفظ الفواتير عبر الـ API
Route::post('/invoices/store', [InvoiceController::class, 'store']);

// مسارات الورديات
Route::post('/shifts/open', [ShiftController::class, 'openShift']);
Route::post('/shifts/close', [ShiftController::class, 'closeShift']);
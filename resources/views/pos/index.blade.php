<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة الكاشير | النظام المحاسبي الأول</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pos-screen.css') }}">
</head>
<body>

<div class="pos-wrapper">
    <div class="receipt-section">
        <div class="receipt-header">
            <button class="btn btn-warning" onclick="holdInvoice()"><i class="fas fa-pause"></i> تعليق</button>
            <button class="btn btn-secondary" onclick="showHeldInvoices()"><i class="fas fa-list"></i> المعلقة ({{ $heldInvoices->count() }})</button>
            <button class="btn btn-danger" onclick="clearCart()"><i class="fas fa-trash"></i> إلغاء</button>
            <a href="{{ route('shifts.index') }}" class="btn" style="background: #17a2b8; text-align: center; text-decoration: none;"><i class="fas fa-box-open"></i> الوردية</a>
            <a href="{{ url('/') }}" class="btn btn-dark"><i class="fas fa-home"></i> خروج</a>
        </div>

        <div class="table-container">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th style="width: 80px;">الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                </tbody>
            </table>
        </div>

        <div class="receipt-totals">
            <div class="totals-row">
                <span>المجموع (قبل الضريبة):</span>
                <span id="subTotalDisplay">0.00</span>
            </div>
            <div class="totals-row">
                <span>الخصم:</span>
                <input type="number" id="discountInput" value="0" min="0" step="0.01" onchange="calculateTotals()">
            </div>
            <div class="totals-row">
                <span>الضريبة (15%):</span>
                <span id="vatTotalDisplay">0.00</span>
            </div>
            <div class="totals-row grand-total">
                <span>الصافي المطلوب:</span>
                <span id="netTotalDisplay">0.00</span>
            </div>
        </div>

        <div class="receipt-actions">
            <button class="btn btn-pay-cash" onclick="openPaymentModal(0)"><i class="fas fa-money-bill-wave"></i> دفع نقدي</button>
            <button class="btn btn-pay-card" onclick="openPaymentModal(1)"><i class="fas fa-credit-card"></i> دفع شبكة</button>
            <button class="btn btn-pay-mix" onclick="openPaymentModal(2)"><i class="fas fa-random"></i> دفع مختلط</button>
        </div>
    </div>

    <div class="items-section">
        <div class="categories-tabs">
            <button class="cat-btn active" onclick="filterCategory('all', this)">الكل</button>
            @foreach($categories as $category)
                <button class="cat-btn" onclick="filterCategory('{{ $category->GUID }}', this)">{{ $category->NAMEAR }}</button>
            @endforeach
        </div>

        <div class="items-grid" id="itemsGrid">
            @foreach($categories as $category)
                @foreach($category->items as $item)
                    <div class="item-card" data-cat="{{ $category->GUID }}" onclick="addItemToCart({{ json_encode($item) }})">
                        @if($item->PATH)
                            <img src="{{ asset('storage/' . $item->PATH) }}" alt="{{ $item->NAME }}">
                        @else
                            <div class="item-no-img"><i class="fas fa-box"></i></div>
                        @endif
                        <div class="item-info">
                            <div class="item-name">{{ $item->NAME }}</div>
                            <div class="item-price">{{ number_format($item->PRICE1, 2) }}</div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>

<div id="paymentModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>إتمام الدفع</h3>
            <span class="close-modal" onclick="closeModal('paymentModal')">&times;</span>
        </div>
        <div class="modal-body">
            <h1 id="modalTotalDue" style="text-align: center; color: #dc3545; font-size: 36px; margin-bottom: 20px;">0.00</h1>
            
            <div id="mixedPaymentFields" style="display: none;">
                <div class="form-group">
                    <label>المدفوع نقداً:</label>
                    <input type="number" id="payCash" class="form-control" value="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>المدفوع شبكة:</label>
                    <input type="number" id="payCard" class="form-control" value="0" step="0.01">
                </div>
            </div>

            <div class="form-group">
                <label>المبلغ المستلم من العميل (للحساب التلقائي):</label>
                <input type="number" id="amountReceived" class="form-control" style="font-size: 24px; font-weight: bold;" onkeyup="calculateChange()">
            </div>
            <div class="form-group">
                <label>المتبقي للعميل (الباقي):</label>
                <input type="text" id="amountChange" class="form-control" style="font-size: 24px; font-weight: bold; color: #28a745;" readonly>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-success" style="width: 100%; font-size: 20px; padding: 15px;" onclick="submitInvoice()">تأكيد الدفع وطباعة الفاتورة <i class="fas fa-print"></i></button>
        </div>
    </div>
</div>

<script src="{{ asset('js/pos/pos.js') }}"></script>
</body>
</html>
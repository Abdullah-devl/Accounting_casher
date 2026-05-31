let rowIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    // إضافة سطر افتراضي عند فتح الشاشة
    addNewRow();
});

// 1. إضافة سطر جديد للجدول (Editable Table)
function addNewRow() {
    rowIndex++;
    const tbody = document.getElementById('invoiceTableBody');
    const tr = document.createElement('tr');
    tr.id = `row_${rowIndex}`;
    
    tr.innerHTML = `
        <td>${rowIndex}</td>
        <td>
            <input type="text" class="search-input" list="itemsList" id="search_${rowIndex}" placeholder="ابحث بالاسم أو الباركود..." onchange="setItemData(${rowIndex})" autocomplete="off">
            <input type="hidden" class="item-id-input" id="item_id_${rowIndex}">
        </td>
        <td><input type="text" id="unit_${rowIndex}" readonly style="color: #666;"></td>
        <td><input type="number" id="qty_${rowIndex}" value="1" min="1" step="0.01" onkeyup="calculateRow(${rowIndex})" onchange="calculateRow(${rowIndex})"></td>
        <td><input type="number" id="price_${rowIndex}" value="0" step="0.01" onkeyup="calculateRow(${rowIndex})" onchange="calculateRow(${rowIndex})"></td>
        <td><input type="number" id="discount_${rowIndex}" value="0" step="0.01" onkeyup="calculateRow(${rowIndex})" onchange="calculateRow(${rowIndex})"></td>
        <td><input type="text" id="total_${rowIndex}" readonly class="row-total" value="0.00" style="font-weight: bold;"></td>
        <td><button type="button" class="btn-danger-icon" onclick="removeRow(${rowIndex})"><i class="fas fa-trash"></i></button></td>
    `;
    
    tbody.appendChild(tr);
    
    // تركيز المؤشر على الحقل الجديد لسرعة الإدخال
    document.getElementById(`search_${rowIndex}`).focus();
}

// 2. تعبئة بيانات الصنف تلقائياً عند اختياره من القائمة المنسدلة (Autocomplete)
function setItemData(id) {
    const searchInput = document.getElementById(`search_${id}`).value;
    const datalist = document.getElementById('itemsList');
    let selectedOption = null;

    // البحث عن الـ option المطابق للنص المدخل
    for (let i = 0; i < datalist.options.length; i++) {
        if (datalist.options[i].value === searchInput) {
            selectedOption = datalist.options[i];
            break;
        }
    }

    if (selectedOption) {
        document.getElementById(`item_id_${id}`).value = selectedOption.getAttribute('data-id');
        document.getElementById(`unit_${id}`).value = selectedOption.getAttribute('data-unit');
        document.getElementById(`price_${id}`).value = selectedOption.getAttribute('data-price');
        
        calculateRow(id);
        
        // بمجرد اختيار الصنف، إضافة سطر جديد تلقائياً لزيادة سرعة إدخال المحاسب
        addNewRow();
    }
}

// 3. الحساب اللحظي للسطر
function calculateRow(id) {
    let qty = parseFloat(document.getElementById(`qty_${id}`).value) || 0;
    let price = parseFloat(document.getElementById(`price_${id}`).value) || 0;
    let discount = parseFloat(document.getElementById(`discount_${id}`).value) || 0;

    let total = (qty * price) - discount;
    document.getElementById(`total_${id}`).value = total.toFixed(2);
    
    calculateGrandTotals();
}

function removeRow(id) {
    const row = document.getElementById(`row_${id}`);
    row.remove();
    calculateGrandTotals();
}

// 4. حساب الإجماليات السفلية
function calculateGrandTotals() {
    let subTotal = 0;
    let totalDiscount = 0;
    
    // جمع كل الخصومات على مستوى الأسطر
    const rows = document.querySelectorAll('#invoiceTableBody tr');
    rows.forEach(row => {
        const rowId = row.id.split('_')[1];
        let qty = parseFloat(document.getElementById(`qty_${rowId}`).value) || 0;
        let price = parseFloat(document.getElementById(`price_${rowId}`).value) || 0;
        let discount = parseFloat(document.getElementById(`discount_${rowId}`).value) || 0;
        
        subTotal += (qty * price);
        totalDiscount += discount;
    });

    // حساب الضريبة (بناءً على نسبة نوع الفاتورة المختار)
    const typeSelect = document.getElementById('type_id');
    let vatPercentage = 0;
    if (typeSelect.selectedIndex > 0) {
        vatPercentage = parseFloat(typeSelect.options[typeSelect.selectedIndex].getAttribute('data-vat')) || 0;
    }

    let netBeforeVat = subTotal - totalDiscount;
    let totalVat = netBeforeVat * (vatPercentage / 100);
    let finalNet = netBeforeVat + totalVat;

    document.getElementById('sub_total').value = subTotal.toFixed(2);
    document.getElementById('total_discount').value = totalDiscount.toFixed(2);
    document.getElementById('total_vat').value = totalVat.toFixed(2);
    document.getElementById('net_amount').value = finalNet.toFixed(2);
}

// إعادة الحساب عند تغيير نوع الفاتورة (لتغيير نسبة الضريبة)
document.getElementById('type_id').addEventListener('change', calculateGrandTotals);

// 5. تجميع البيانات وإرسالها للسيرفر عبر AJAX
function saveGeneralInvoice() {
    const form = document.getElementById('generalInvoiceForm');
    
    // التحقق من الحقول الإجبارية
    if (!form.reportValidity()) return;

    // تجميع عناصر الجدول
    let items = [];
    const rows = document.querySelectorAll('#invoiceTableBody tr');
    
    rows.forEach(row => {
        const rowId = row.id.split('_')[1];
        const itemId = document.getElementById(`item_id_${rowId}`).value;
        
        // تجاهل الأسطر الفارغة
        if (itemId) {
            items.push({
                item_id: itemId,
                unit: document.getElementById(`unit_${rowId}`).value,
                qty: document.getElementById(`qty_${rowId}`).value,
                price: document.getElementById(`price_${rowId}`).value,
                discount: document.getElementById(`discount_${rowId}`).value,
                total: document.getElementById(`total_${rowId}`).value
            });
        }
    });

    if (items.length === 0) {
        alert("يجب إدخال صنف واحد على الأقل في الفاتورة.");
        return;
    }

    let payload = {
        type_id: document.getElementById('type_id').value,
        invoice_number: document.getElementById('invoice_number').value,
        invoice_date: document.getElementById('invoice_date').value,
        payment_method: document.getElementById('payment_method').value,
        account_id: document.getElementById('account_id').value,
        note: document.getElementById('note').value,
        total_amount: document.getElementById('sub_total').value,
        total_discount: document.getElementById('total_discount').value,
        total_vat: document.getElementById('total_vat').value,
        net_amount: document.getElementById('net_amount').value,
        items: items
    };

    fetch('/general-invoices/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.redirect; // العودة لقائمة الفواتير
        } else {
            alert(data.message);
        }
    });
}

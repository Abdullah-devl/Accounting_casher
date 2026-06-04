/* =========================================
   ملف العمليات الحسابية والديناميكية للكاشير
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    // متغير لتتبع أرقام الصفوف (Index) حتى لا تتداخل أسماء المصفوفات عند الإرسال
    let rowCount = 1; 

    const tbody = document.getElementById('items-tbody');
    const btnAddRow = document.getElementById('btn-add-row');
    const posForm = document.querySelector('form');

    // 0. منع إرسال الفورم عند الضغط على Enter في حقول الإدخال لتفادي الحفظ بالخطأ
    if (posForm) {
        posForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit') {
                e.preventDefault();
            }
        });
    }

    // 1. إضافة سطر جديد عند الضغط على الزر
    btnAddRow.addEventListener('click', function() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        
        // بناء الـ HTML للسطر الجديد مع زيادة رقم الـ Index
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowCount}][item_id]" class="form-control input-item-id" placeholder="كود الصنف أو الباركود">
                <input type="hidden" name="items[${rowCount}][cost]" value="0">
            </td>
            <td><input type="text" name="items[${rowCount}][unit]" class="form-control input-unit" value="حبة"></td>
            <td><input type="number" name="items[${rowCount}][qty]" class="form-control input-qty" value="1" min="1"></td>
            <td><input type="number" name="items[${rowCount}][price]" class="form-control input-price" value="0" min="0" step="0.01"></td>
            <td><input type="number" name="items[${rowCount}][vat]" class="form-control input-vat" value="15" readonly></td>
            <td><input type="number" name="items[${rowCount}][total]" class="form-control input-total" value="0" readonly></td>
            <td><button type="button" class="btn btn-danger btn-remove"><i class="fas fa-trash"></i> حذف</button></td>
        `;
        
        tbody.appendChild(tr);
        
        // التركيز التلقائي على حقل الإدخال الجديد
        tr.querySelector('.input-item-id').focus();
        
        rowCount++;
    });

    // 2. معالجة الأحداث الديناميكية (تغيير الكمية/السعر، وحذف السطر)
    tbody.addEventListener('input', function(e) {
        if (e.target.classList.contains('input-qty') || e.target.classList.contains('input-price') || e.target.classList.contains('input-vat')) {
            calculateRow(e.target.closest('tr'));
            calculateGrandTotals();
        }
    });

    tbody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove')) {
            const row = e.target.closest('tr');
            row.remove();
            calculateGrandTotals(); // إعادة حساب الإجماليات بعد الحذف
        }
    });

    // 3. جلب بيانات المنتج تلقائياً عند مسح الباركود أو كتابة الرقم
    tbody.addEventListener('change', function(e) {
        if (e.target.classList.contains('input-item-id')) {
            const row = e.target.closest('tr');
            const itemCode = e.target.value.trim();
            if (itemCode.length > 0) {
                fetchItemDetails(itemCode, row);
            }
        }
    });

    tbody.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('input-item-id')) {
            e.preventDefault();
            e.target.blur(); // سيؤدي هذا لتشغيل حدث change تلقائياً بشكل آمن
        }
    });

    // دالة استدعاء بيانات الصنف من السيرفر
    function fetchItemDetails(code, row) {
        fetch(`/pos/scan/${code}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = data.item;
                    // تعبئة البيانات في الحقول المطابقة بالسطر
                    row.querySelector('.input-item-id').value = item.barcode1 || item.NUMBER || item.GUID;
                    row.querySelector('input[name*="[cost]"]').value = item.COST1 || 0;
                    row.querySelector('.input-unit').value = item.UNITE1 || 'حبة';
                    row.querySelector('.input-price').value = item.PRICE1 || 0;
                    row.querySelector('.input-vat').value = item.PER !== undefined ? item.PER : 15;
                    
                    // إذا كانت الكمية المدخلة غير صحيحة، نضعها 1
                    const qtyInput = row.querySelector('.input-qty');
                    if (parseFloat(qtyInput.value) <= 0 || !qtyInput.value) {
                        qtyInput.value = 1;
                    }

                    // حساب السطر وإجمالي الفاتورة
                    calculateRow(row);
                    calculateGrandTotals();

                    // التركيز وتحديد كمية الصنف للتسهيل على الكاشير
                    qtyInput.focus();
                    qtyInput.select();
                } else {
                    alert('لم يتم العثور على الصنف!');
                    row.querySelector('.input-item-id').value = '';
                    row.querySelector('.input-item-id').focus();
                }
            })
            .catch(error => {
                console.error('Error fetching item details:', error);
                alert('حدث خطأ أثناء الاتصال بقاعدة البيانات، يرجى المحاولة لاحقاً.');
            });
    }

    // 4. دالة حساب إجمالي السطر الواحد
    function calculateRow(row) {
        const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
        const price = parseFloat(row.querySelector('.input-price').value) || 0;
        const vatRate = parseFloat(row.querySelector('.input-vat').value) || 0;

        // حساب السعر قبل الضريبة للسطر
        const rowSubTotal = qty * price;
        // حساب الضريبة للسطر
        const rowVat = rowSubTotal * (vatRate / 100);
        // الصافي للسطر
        const rowNetTotal = rowSubTotal + rowVat;

        // تحديث حقل الإجمالي في نفس السطر
        row.querySelector('.input-total').value = rowNetTotal.toFixed(2);
    }

    // 5. دالة حساب الإجماليات الختامية للفاتورة بالكامل
    function calculateGrandTotals() {
        let grandSubTotal = 0;
        let grandVat = 0;

        const rows = document.querySelectorAll('.item-row');
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            const price = parseFloat(row.querySelector('.input-price').value) || 0;
            const vatRate = parseFloat(row.querySelector('.input-vat').value) || 0;

            const rowSubTotal = qty * price;
            const rowVat = rowSubTotal * (vatRate / 100);

            grandSubTotal += rowSubTotal;
            grandVat += rowVat;
        });

        const grandNetTotal = grandSubTotal + grandVat;

        // تحديث الحقول السفلية
        document.getElementById('total_amount').value = grandSubTotal.toFixed(2);
        document.getElementById('total_vat').value = grandVat.toFixed(2);
        document.getElementById('net_amount').value = grandNetTotal.toFixed(2);
    }

});
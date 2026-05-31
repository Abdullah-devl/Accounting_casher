let cart = [];
let currentPaymentMethod = 0; // 0 Cash, 1 Card, 2 Mixed

// 1. إضافة صنف للفاتورة (من الأزرار أو الباركود)
function addItemToCart(item) {
    // البحث هل الصنف موجود مسبقاً في السلة؟
    let existingItem = cart.find(i => i.GUID === item.GUID);
    
    if (existingItem) {
        existingItem.qty += 1;
        existingItem.total = existingItem.qty * existingItem.price;
    } else {
        // حساب الضريبة إذا كانت مفعلة (ممثلة بـ CT_PER و PER)
        let itemVat = item.CT_PER ? (item.PRICE1 * (item.PER / 100)) : 0;
        
        cart.push({
            GUID: item.GUID,
            name: item.NAME,
            unit1: item.UNITE1,
            price: item.PRICE1,
            qty: 1,
            vat: itemVat,
            total: item.PRICE1
        });
    }
    renderCart();
}

// 2. رسم جدول الفاتورة وحساب الإجماليات
function renderCart() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    
    let subTotal = 0;
    let totalVat = 0;

    cart.forEach((item, index) => {
        subTotal += item.total;
        totalVat += (item.vat * item.qty);

        tbody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>
                    <div class="qty-control">
                        <button class="btn-qty" onclick="updateQty(${index}, 1)">+</button>
                        <input type="number" class="qty-input" value="${item.qty}" onchange="setQty(${index}, this.value)">
                        <button class="btn-qty" style="background:#dc3545;" onclick="updateQty(${index}, -1)">-</button>
                    </div>
                </td>
                <td>${parseFloat(item.price).toFixed(2)}</td>
                <td>${parseFloat(item.total).toFixed(2)}</td>
                <td><button class="btn btn-danger" style="padding: 5px 10px;" onclick="removeItem(${index})"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
    });

    // تحديث الشاشات الرقمية
    let discount = parseFloat(document.getElementById('discountInput').value) || 0;
    let netTotal = subTotal - discount; // بفرض أن الإجمالي شامل الضريبة

    document.getElementById('subTotalDisplay').innerText = subTotal.toFixed(2);
    document.getElementById('vatTotalDisplay').innerText = totalVat.toFixed(2);
    document.getElementById('netTotalDisplay').innerText = netTotal.toFixed(2);
    document.getElementById('modalTotalDue').innerText = netTotal.toFixed(2);
}

// 3. التعديل اللحظي للكميات والحذف
function updateQty(index, change) {
    cart[index].qty += change;
    if (cart[index].qty <= 0) {
        removeItem(index);
        return;
    }
    cart[index].total = cart[index].qty * cart[index].price;
    renderCart();
}

// 4. تعيين الكمية مباشرة
function setQty(index, val) {
    let newQty = parseFloat(val);
    if (newQty <= 0) { removeItem(index); return; }
    cart[index].qty = newQty;
    cart[index].total = cart[index].qty * cart[index].price;
    renderCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function clearCart() {
    if(confirm('هل أنت متأكد من إلغاء الفاتورة بالكامل؟')) {
        cart = [];
        document.getElementById('discountInput').value = 0;
        renderCart();
    }
}

function calculateTotals() { renderCart(); }

// 5. قارئ الباركود المخفي والمفعل دائماً
let barcodeBuffer = '';
let barcodeTimeout;
document.addEventListener('keypress', function(e) {
    // إذا كان المستخدم يكتب في حقل بحث أو كمية، نتجاهل الباركود
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

    if (e.key === 'Enter') {
        if (barcodeBuffer.length > 2) {
            fetchItemByBarcode(barcodeBuffer);
        }
        barcodeBuffer = '';
    } else {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimeout);
        barcodeTimeout = setTimeout(() => { barcodeBuffer = ''; }, 300); // تفريغ الذاكرة إذا تأخر
    }
});

function fetchItemByBarcode(code) {
    fetch(`/pos/scan/${code}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addItemToCart(data.item);
            } else {
                alert('لم يتم العثور على الصنف!');
            }
        });
}

// 6. فلترة الأزرار السريعة بالتصنيف
function filterCategory(catId, btnElement) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btnElement.classList.add('active');

    const items = document.querySelectorAll('.item-card');
    items.forEach(item => {
        if (catId === 'all' || item.getAttribute('data-cat') == catId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// 7. نوافذ الدفع السريع والتعليق
function openPaymentModal(method) {
    if (cart.length === 0) { alert('الفاتورة فارغة!'); return; }
    currentPaymentMethod = method;
    document.getElementById('amountReceived').value = '';
    document.getElementById('amountChange').value = '0.00';
    
    if (method === 2) { // مختلط
        document.getElementById('mixedPaymentFields').style.display = 'block';
    } else {
        document.getElementById('mixedPaymentFields').style.display = 'none';
    }
    
    document.getElementById('paymentModal').style.display = 'flex';
    document.getElementById('amountReceived').focus();
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function calculateChange() {
    let due = parseFloat(document.getElementById('modalTotalDue').innerText);
    let received = parseFloat(document.getElementById('amountReceived').value) || 0;
    let change = received - due;
    document.getElementById('amountChange').value = change > 0 ? change.toFixed(2) : '0.00';
}

// 8. إرسال الفاتورة للسيرفر (Save & Print)
function submitInvoice(isWaiting = false) {
    if (cart.length === 0) return;

    let payload = {
        items: cart,
        payment_method: currentPaymentMethod,
        total_amount: parseFloat(document.getElementById('subTotalDisplay').innerText),
        total_discount: parseFloat(document.getElementById('discountInput').value) || 0,
        total_vat: parseFloat(document.getElementById('vatTotalDisplay').innerText),
        net_amount: parseFloat(document.getElementById('netTotalDisplay').innerText),
        is_waiting: isWaiting
    };

    fetch('/pos/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            cart = []; // تفريغ السلة استعداداً للزبون التالي
            document.getElementById('discountInput').value = 0;
            renderCart();
            closeModal('paymentModal');
        } else {
            alert(data.message);
        }
    });
}

// تعليق الفاتورة مباشرة
function holdInvoice() {
    if (cart.length === 0) { alert('الفاتورة فارغة!'); return; }
    if(confirm('هل تريد تعليق هذه الفاتورة؟')) {
        submitInvoice(true); // إرسال مع تفعيل is_waiting=true
    }
}
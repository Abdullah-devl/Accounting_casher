// 1. تصفية وبحث سريع في الجدول (DataGrid Filter)
function filterTable() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const table = document.getElementById("itemsTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        // نبحث في عمود الاسم (رقم 1) وعمود الباركود (رقم 3)
        let tdName = tr[i].getElementsByTagName("td")[1];
        let tdBarcode = tr[i].getElementsByTagName("td")[3];
        
        if (tdName || tdBarcode) {
            let txtName = tdName.textContent || tdName.innerText;
            let txtBarcode = tdBarcode.textContent || tdBarcode.innerText;
            
            if (txtName.toUpperCase().indexOf(input) > -1 || txtBarcode.toUpperCase().indexOf(input) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }       
    }
}

// 2. تبويبات نموذج الإدخال (Form Tabs)
function showFormTab(tabId) {
    const contents = document.querySelectorAll('.ftab-content');
    contents.forEach(c => c.classList.remove('active'));
    
    const btns = document.querySelectorAll('.ftab-btn');
    btns.forEach(b => b.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

// 3. فتح نافذة الإضافة الفارغة
function openCreateModal() {
    document.getElementById('itemForm').reset();
    document.getElementById('modalTitle').innerText = 'إضافة بطاقة صنف جديدة';
    document.getElementById('itemForm').action = '/items';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('freezeContainer').style.display = 'none'; // نخفي خيار التجميد عند الإنشاء
    
    // إرجاع التبويب الأول كافتراضي
    showFormTab('basicTab');
    document.querySelectorAll('.ftab-btn')[0].classList.add('active');
    
    document.getElementById('itemModal').style.display = 'flex';
}

// 4. فتح نافذة التعديل مع تعبئة البيانات تلقائياً
function openEditModal(item) {
    document.getElementById('modalTitle').innerText = 'تعديل بيانات الصنف';
    document.getElementById('itemForm').action = '/items/' + item.id;
    document.getElementById('formMethod').value = 'PUT'; // نستخدم PUT للتعديل في لارافل
    
    // تعبئة البيانات الأساسية
    document.getElementById('inp_name').value = item.name;
    document.getElementById('inp_category').value = item.category_id || '';
    
    // تعبئة بيانات الوحدة 1
    document.getElementById('inp_b1').value = item.barcode1 || '';
    document.getElementById('inp_u1').value = item.unit1 || '';
    document.getElementById('inp_c1').value = item.cost1 || 0;
    document.getElementById('inp_p1').value = item.price1 || 0;

    // تعبئة بيانات الوحدة 2
    document.getElementById('inp_b2').value = item.barcode2 || '';
    document.getElementById('inp_u2').value = item.unit2 || '';
    document.getElementById('inp_q2').value = item.qty2 || '';
    document.getElementById('inp_c2').value = item.cost2 || 0;
    document.getElementById('inp_p2').value = item.price2 || 0;

    // تعبئة بيانات الوحدة 3
    document.getElementById('inp_b3').value = item.barcode3 || '';
    document.getElementById('inp_u3').value = item.unit3 || '';
    document.getElementById('inp_q3').value = item.qty3 || '';
    document.getElementById('inp_c3').value = item.cost3 || 0;
    document.getElementById('inp_p3').value = item.price3 || 0;

    // تعبئة الإعدادات
    document.getElementById('inp_min').value = item.min_order_qty || 0;
    document.getElementById('inp_exp').checked = item.has_expiry_date == 1;
    document.getElementById('inp_tax').checked = item.tax_active == 1;
    
    // إظهار وتعبئة خيار التجميد
    document.getElementById('freezeContainer').style.display = 'inline-block';
    document.getElementById('inp_freeze').checked = item.is_frozen == 1;

    showFormTab('basicTab');
    document.querySelectorAll('.ftab-btn')[0].classList.add('active');

    document.getElementById('itemModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
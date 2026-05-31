let rowIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    // إضافة سطرين افتراضيين (لأن القيد يتطلب مدين ودائن على الأقل)
    addNewRow();
    addNewRow();
    checkBalance();
});

// 1. إضافة سطر جديد للقيد
function addNewRow() {
    rowIndex++;
    const tbody = document.getElementById('journalBody');
    const tr = document.createElement('tr');
    tr.id = `row_${rowIndex}`;
    
    tr.innerHTML = `
        <td>
            <input type="text" list="accountsList" id="search_${rowIndex}" placeholder="اختر الحساب..." onchange="setAccountData(${rowIndex})" autocomplete="off">
            <input type="hidden" id="account_id_${rowIndex}">
        </td>
        <td><input type="number" id="debit_${rowIndex}" class="input-debit" value="0.00" step="0.01" min="0" onkeyup="calculateTotals()" onchange="calculateTotals()"></td>
        <td><input type="number" id="credit_${rowIndex}" class="input-credit" value="0.00" step="0.01" min="0" onkeyup="calculateTotals()" onchange="calculateTotals()"></td>
        <td><input type="text" id="note_${rowIndex}" placeholder="شرح السطر..."></td>
        <td><button type="button" class="btn-danger-icon" onclick="removeRow(${rowIndex})"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
}

// 2. تعبئة الحساب المخفي بناءً على الأوتوكومبليت
function setAccountData(id) {
    const searchInput = document.getElementById(`search_${id}`).value;
    const datalist = document.getElementById('accountsList');
    
    for (let i = 0; i < datalist.options.length; i++) {
        if (datalist.options[i].value === searchInput) {
            document.getElementById(`account_id_${id}`).value = datalist.options[i].getAttribute('data-id');
            break;
        }
    }
}

function removeRow(id) {
    const tbody = document.getElementById('journalBody');
    if (tbody.children.length <= 2) {
        alert("القيد يجب أن يحتوي على سطرين كحد أدنى.");
        return;
    }
    document.getElementById(`row_${id}`).remove();
    calculateTotals();
}

// 3. حساب المجاميع والتحقق من التوازن (Validation)
function calculateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;

    const rows = document.querySelectorAll('#journalBody tr');
    rows.forEach(row => {
        const id = row.id.split('_')[1];
        
        // منع إدخال مدين ودائن في نفس السطر (المحاسبة لا تقبل ذلك عادة للسطر الواحد)
        let debitInput = document.getElementById(`debit_${id}`);
        let creditInput = document.getElementById(`credit_${id}`);
        
        if (parseFloat(debitInput.value) > 0) creditInput.value = "0.00";
        if (parseFloat(creditInput.value) > 0) debitInput.value = "0.00";

        totalDebit += parseFloat(debitInput.value) || 0;
        totalCredit += parseFloat(creditInput.value) || 0;
    });

    document.getElementById('total_debit').value = totalDebit.toFixed(2);
    document.getElementById('total_credit').value = totalCredit.toFixed(2);
    
    checkBalance();
}

function checkBalance() {
    let debit = parseFloat(document.getElementById('total_debit').value);
    let credit = parseFloat(document.getElementById('total_credit').value);
    let diff = Math.abs(debit - credit);
    
    document.getElementById('total_diff').value = diff.toFixed(2);
    
    const diffBox = document.getElementById('diffBox');
    const statusBadge = document.getElementById('balanceStatus');
    const saveBtn = document.querySelector('.btn-success');

    if (debit === credit && debit > 0) {
        // القيد متزن
        diffBox.classList.add('balanced');
        statusBadge.classList.add('balanced');
        statusBadge.innerText = 'متزن ومطابق';
        saveBtn.disabled = false;
    } else {
        // القيد غير متزن
        diffBox.classList.remove('balanced');
        statusBadge.classList.remove('balanced');
        statusBadge.innerText = 'غير متزن!';
        saveBtn.disabled = true; // تعطيل زر الحفظ
    }
}

// 4. إرسال القيد للسيرفر
function saveJournal() {
    let items = [];
    let isValid = true;

    const rows = document.querySelectorAll('#journalBody tr');
    rows.forEach(row => {
        const id = row.id.split('_')[1];
        const accountId = document.getElementById(`account_id_${id}`).value;
        const debit = parseFloat(document.getElementById(`debit_${id}`).value) || 0;
        const credit = parseFloat(document.getElementById(`credit_${id}`).value) || 0;
        const note = document.getElementById(`note_${id}`).value;

        // إذا كان هناك مبلغ مدخل، يجب أن يكون هناك حساب مالي مختار
        if ((debit > 0 || credit > 0) && !accountId) {
            isValid = false;
        }

        if (debit > 0 || credit > 0) {
            items.push({
                account_id: accountId,
                debit: debit,
                credit: credit,
                note: note
            });
        }
    });

    if (!isValid) {
        alert("يرجى التأكد من اختيار حساب مالي صالح لكل سطر يحتوي على مبالغ.");
        return;
    }

    let payload = {
        entry_number: document.getElementById('entry_number').value,
        entry_date: document.getElementById('entry_date').value,
        note: document.getElementById('main_note').value,
        items: items
    };

    fetch('/journal-entries/store', {
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
            window.location.href = data.redirect;
        } else {
            alert(data.message);
        }
    });
}
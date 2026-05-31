    document.addEventListener('DOMContentLoaded', function() {
    
    // 1. منطق توسيع وطي شجرة الحسابات
    const togglers = document.querySelectorAll('.toggle-icon');
    
    togglers.forEach(toggler => {
        toggler.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const nestedTree = this.closest('.tree-node').querySelector('.nested-tree');
            
            if (nestedTree) {
                nestedTree.classList.toggle('active');
                if (nestedTree.classList.contains('active')) {
                    icon.classList.remove('fa-plus-square');
                    icon.classList.add('fa-minus-square');
                } else {
                    icon.classList.remove('fa-minus-square');
                    icon.classList.add('fa-plus-square');
                }
            }
        });
    });

});

// 2. دوال النوافذ المنبثقة (Modals)
function openCreateModal(parentId, parentName) {
    document.getElementById('parentIdInput').value = parentId;
    document.getElementById('parentNameDisplay').innerText = parentName;
    
    const modal = document.getElementById('createModal');
    modal.style.display = 'flex';
}

function openEditModal(accountId, currentName, isFrozen) {
    document.getElementById('editNameInput').value = currentName;
    document.getElementById('editFrozenInput').checked = isFrozen;
    
    // تغيير رابط الـ Form ليتوجه إلى دالة الـ Update مع الـ ID الصحيح
    const form = document.getElementById('editForm');
    form.action = `/accounts/${accountId}`;
    
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
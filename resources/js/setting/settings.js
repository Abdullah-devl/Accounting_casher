/* =========================================
   العمليات الديناميكية لشاشة الإعدادات
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    // يمكنك إضافة أي دوال تهيئة هنا إذا احتجت مستقبلاً
});

// دالة التبديل بين التبويبات (Tabs)
function openTab(evt, tabName) {
    let i, tabcontent, tablinks;
    
    // 1. إخفاء جميع محتويات التبويبات
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    
    // 2. إزالة حالة (النشط) من جميع الأزرار
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    
    // 3. إظهار التبويب المطلوب وتنشيط الزر الخاص به
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}
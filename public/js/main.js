/* =========================================
   العمليات الديناميكية العامة للهيكل (Master)
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. تحديد الزر النشط في القائمة الجانبية تلقائياً بناءً على الرابط الحالي
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('.sidebar ul li a');
    
    navLinks.forEach(link => {
        // إزالة الكلاس النشط من الجميع أولاً
        link.classList.remove('active');
        
        // إذا كان رابط الزر يطابق الرابط الحالي في المتصفح، اجعله نشطاً
        if (link.href === currentUrl) {
            link.classList.add('active');
        }
    });

});
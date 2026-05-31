# دليل مواصفات وإعادة بناء النظام (MgaSoftAccounts)

يحتوي هذا الملف على توثيق تفصيلي للنظام المحاسبي ونقاط البيع لمساعدة المطورين على فهم هيكلية التطبيق الحالي وكيفية ترحيله وإعادة بنائه باستخدام تقنيات حديثة.

---

## 1. تحليل النظام الحالي (Current System Analysis)

### 1.1 معمارية وتصميم النظام الحالي
*   **طبيعة النظام**: تطبيق مكتبي لإدارة الحسابات والمستودعات ونقاط البيع (Desktop ERP & POS).
*   **التقنيات المستخدمة**:
    *   **لغة التطوير**: Visual Basic .NET (`VB.NET`).
    *   **إطار العمل**: `.NET Framework 4.7.2`.
    *   **قاعدة البيانات**: `Microsoft SQL Server`.
    *   **الواجهة الرسومية**: نماذج ويندوز تقليدية (Windows Forms) مدعومة بمكتبات `CTSkinet` و `Bunifu Animator` لتقديم مؤثرات بصرية وانتقالات حركية سلسة.
*   **نمط الربط**:
    *   يتم الاتصال بقاعدة البيانات محلياً أو عبر شبكة محلية (Client-Server) عن طريق قراءة خادم SQL واسم قاعدة البيانات وبيانات الاعتماد من ملف محلي مشفر باسم `CONN.dll` بجوار الملف التنفيذي.
    *   يعتمد النظام على **الإجراءات المخزنة (Stored Procedures)** لتنفيذ كافة العمليات الأساسية وإجراء العمليات الحسابية داخل خادم قاعدة البيانات لتوفير سرعة معالجة عالية.

### 1.2 الأقسام التشغيلية والوظيفية للنظام
1.  **إدارة الحسابات والدليل المحاسبي**:
    *   شجرة حسابات هرمية غير محدودة المستويات (أصول، خصوم، حقوق ملكية، مصاريف، إيرادات).
    *   حسابات عملاء وموردين وحسابات نقدية وصناديق.
    *   نظام القيود المزدوجة التلقائية واليدوية لتسوية الحسابات.
2.  **إدارة المستودعات والمواد**:
    *   تعريف المواد وتصنيفها في مجموعات.
    *   دعم الصنف الواحد لـ **ثلاث وحدات بيع** مختلفة (مثال: حبة، علبة، كرتون) مع باركود مستقل وسعر مبيع وتكلفة منفصلة لكل وحدة.
    *   إدارة تواريخ الإنتاج والانتهاء والتحذير قبل انتهاء الصلاحية.
3.  **إدارة المبيعات ونقاط البيع (POS)**:
    *   واجهة كاشير سريعة لإصدار الفواتير المبسطة تدعم شاشات اللمس وقراءة الباركود.
    *   إدارة ورديات الموظفين (فتح وإغلاق الصناديق وجرد الدرج اليومي للمبيعات).
    *   إدارة الفواتير المعلقة (فواتير قيد الانتظار).
4.  **التقارير والمخرجات**:
    *   تقارير مالية (أرصدة الحسابات، كشوفات الحسابات التفصيلية، دفتر اليومية العام).
    *   تقارير مخزنية (حركة المواد، أرصدة المواد والكميات، أرباح الفواتير).
    *   النسخ الاحتياطي اليدوي والتلقائي لقاعدة البيانات.

---

## 2. قاعدة البيانات والترحيل (Database Migration)

في حالة الرغبة بترحيل قاعدة البيانات من SQL Server الحالي إلى خادم حديث أو استخدام نظام إدارة قواعد بيانات مفتوح المصدر مثل **PostgreSQL** أو **MySQL**، إليك مخطط الترحيل والترميز المقترح للجداول.

### 2.1 الجداول الأساسية وهيكليتها بالـ SQL الحديث (PostgreSQL كمثال)

#### جدول معلومات الشركة (`company_info`)
```sql
CREATE TABLE company_info (
    guid VARCHAR(255) PRIMARY KEY,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255),
    phone VARCHAR(50),
    mobile VARCHAR(50),
    address1 TEXT,
    address2 TEXT,
    cr_number VARCHAR(100), -- السجل التجاري
    vat_number VARCHAR(100), -- الرقم الضريبي
    email VARCHAR(100),
    website VARCHAR(100),
    logo BYTEA, -- لحفظ الصورة كمصفوفة بايتات
    logo_path VARCHAR(255)
);
```

#### جدول شجرة الحسابات (`accounts`)
```sql
CREATE TABLE accounts (
    guid VARCHAR(255) PRIMARY KEY,
    code VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_guid VARCHAR(255) REFERENCES accounts(guid) ON DELETE SET NULL,
    end_account DOUBLE PRECISION,
    currency_guid VARCHAR(255), -- يربط بجدول العملات
    mobile VARCHAR(50),
    is_frozen BOOLEAN DEFAULT FALSE,
    account_type INT, -- 0 رئيسي (لا يقبل القيود)، 1 فرعي (يقبل القيود)
    opening_debit DOUBLE PRECISION DEFAULT 0.0,
    opening_credit DOUBLE PRECISION DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### جدول الأصناف والمواد (`items`)
```sql
CREATE TABLE items (
    guid VARCHAR(255) PRIMARY KEY,
    item_number SERIAL,
    name VARCHAR(255) NOT NULL,
    note TEXT,
    group_guid VARCHAR(255), -- مجموعة الصنف
    
    -- بيانات الوحدة الأولى
    barcode1 VARCHAR(100),
    unit1 VARCHAR(100),
    qty1 DOUBLE PRECISION DEFAULT 1.0,
    cost1 DOUBLE PRECISION DEFAULT 0.0,
    price1 DOUBLE PRECISION DEFAULT 0.0,
    
    -- بيانات الوحدة الثانية
    barcode2 VARCHAR(100),
    unit2 VARCHAR(100),
    qty2 DOUBLE PRECISION DEFAULT 0.0,
    cost2 DOUBLE PRECISION DEFAULT 0.0,
    price2 DOUBLE PRECISION DEFAULT 0.0,
    
    -- بيانات الوحدة الثالثة
    barcode3 VARCHAR(100),
    unit3 VARCHAR(100),
    qty3 DOUBLE PRECISION DEFAULT 0.0,
    cost3 DOUBLE PRECISION DEFAULT 0.0,
    price3 DOUBLE PRECISION DEFAULT 0.0,
    
    default_unit INT DEFAULT 1, -- 1 أو 2 أو 3
    production_date DATE,
    expiry_date DATE,
    expiry_warning_days INT DEFAULT 30,
    min_order_qty DOUBLE PRECISION DEFAULT 0.0,
    is_frozen BOOLEAN DEFAULT FALSE,
    image BYTEA,
    currency_guid VARCHAR(255),
    tax_active BOOLEAN DEFAULT TRUE,
    tax_percentage DOUBLE PRECISION DEFAULT 15.0
);
```

#### جداول الفواتير (`bills` & `bill_details`)
```sql
CREATE TABLE bills (
    guid VARCHAR(255) PRIMARY KEY,
    bill_number INT NOT NULL,
    bill_type_code INT NOT NULL, -- 1 مبيعات، 2 مشتريات، 3 مرتجع...
    bill_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_type INT DEFAULT 0, -- 0 نقدي، 1 آجل، 2 شبكة
    customer_name VARCHAR(255),
    note TEXT,
    store_guid VARCHAR(255),
    job_guid VARCHAR(255), -- مركز التكلفة
    currency_guid VARCHAR(255),
    currency_rate DOUBLE PRECISION DEFAULT 1.0,
    total_discount DOUBLE PRECISION DEFAULT 0.0,
    total_vat DOUBLE PRECISION DEFAULT 0.0,
    extra_discount DOUBLE PRECISION DEFAULT 0.0,
    total_final DOUBLE PRECISION DEFAULT 0.0,
    account_guid VARCHAR(255) REFERENCES accounts(guid), -- حساب العميل/المورد المالي
    bill_type_guid VARCHAR(255),
    is_pos INT DEFAULT 0,
    is_waiting BOOLEAN DEFAULT FALSE,
    total_paid DOUBLE PRECISION DEFAULT 0.0,
    total_left DOUBLE PRECISION DEFAULT 0.0
);

CREATE TABLE bill_details (
    id SERIAL PRIMARY KEY,
    parent_guid VARCHAR(255) REFERENCES bills(guid) ON DELETE CASCADE,
    item_guid VARCHAR(255) REFERENCES items(guid),
    qty DOUBLE PRECISION NOT NULL,
    price DOUBLE PRECISION NOT NULL,
    unit VARCHAR(100),
    unit_factor DOUBLE PRECISION DEFAULT 1.0,
    cost DOUBLE PRECISION DEFAULT 0.0,
    earning DOUBLE PRECISION DEFAULT 0.0,
    discount DOUBLE PRECISION DEFAULT 0.0,
    vat DOUBLE PRECISION DEFAULT 0.0,
    total_gross DOUBLE PRECISION NOT NULL,
    total_net DOUBLE PRECISION NOT NULL
);
```

#### جداول قيود اليومية (`journal_entries` & `journal_details`)
```sql
CREATE TABLE journal_entries (
    guid VARCHAR(255) PRIMARY KEY,
    entry_type_code INT,
    entry_number INT,
    entry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note TEXT,
    job_guid VARCHAR(255),
    currency_guid VARCHAR(255),
    currency_rate DOUBLE PRECISION DEFAULT 1.0
);

CREATE TABLE journal_details (
    id SERIAL PRIMARY KEY,
    parent_guid VARCHAR(255) REFERENCES journal_entries(guid) ON DELETE CASCADE,
    account_guid VARCHAR(255) REFERENCES accounts(guid),
    debit DOUBLE PRECISION DEFAULT 0.0,
    credit DOUBLE PRECISION DEFAULT 0.0,
    note TEXT,
    job_guid VARCHAR(255),
    currency_guid VARCHAR(255),
    currency_rate DOUBLE PRECISION DEFAULT 1.0,
    value_local DOUBLE PRECISION DEFAULT 0.0 -- القيمة بالعملة المحلية بعد الصرف
);
```

---

## 3. بناء واجهات المستخدم (Frontend)

تعتمد واجهة المستخدم القديمة على شبكات إدخال متسلسلة تعتمد على مفاتيح الكيبورد وحدث `KeyPress` لضمان إدخال البيانات بسرعة من قبل المحاسب دون الحاجة للموس.

### 3.1 استراتيجيات واجهات المستخدم الحديثة المقترحة
إذا تم تحديث النظام لواجهات الويب، يفضل استخدام تقنيات عصرية مثل **React** أو **Vue.js** مع مكتبات مكونات مخصصة تضمن الكفاءة العالية وسرعة الاستجابة.

1.  **شاشة الكاشير السريعة (POS UI)**:
    *   **توزيع العناصر**:
        *   الجهة اليسرى: شبكة عرض المنتجات المضافة للفاتورة الحالية مع أزرار سريعة لتعديل الكمية (`+` و `-`) وحذف الصنف وزر لتطبيق خصم أو تعليق الفاتورة.
        *   الجهة اليمنى: قائمة منسدلة للمجموعات (الأقسام) تليها شبكة أزرار الأصناف (مع إمكانية إسناد صور ملونة وخلفيات مخصصة لكل صنف).
        *   الجهة السفلى: أزرار الدفع السريع (نقدي، بطاقة، دفع مختلط) مع إجمالي الفاتورة وقيمة الضريبة بخط عريض وواضح.
    *   **تسهيل الإدخال**: تفعيل دعم قارئ الباركود (Barcode Scanner) بشكل غير متزامن بحيث يركز النظام تلقائياً على حقل مخفي لاستقبال الباركود وإضافته مباشرة للشبكة.
2.  **شاشة شجرة الحسابات (Chart of Accounts UI)**:
    *   عرض الحسابات على هيئة جدول شجري (Tree Grid).
    *   إمكانية التوسيع والطي لكل مستوى.
    *   أزرار سريعة لإجراء العمليات على كل حساب (إضافة حساب فرع، تعديل الاسم، عرض كشف حساب تفصيلي).
3.  **شاشات الفواتير والقيود (Invoice & Journal Input)**:
    *   جداول إدخال ديناميكية تفاعلية (Editable Tables) تدعم التنقل السريع بين الخلايا بواسطة زر `Tab` أو الأسهم.
    *   قائمة إكمال تلقائي (Autocomplete Dropdown) سريعة للبحث عن المواد بالاسم أو الكود أو الباركود.

---

## 4. برمجة الواجهة الخلفية (Backend)

لترحيل تطبيق VB.NET القديم إلى واجهة خلفية حديثة ومستقلة، يفضل بناء **RESTful API** باستخدام **ASP.NET Core Web API** أو **Node.js (Express/NestJS)**.

### 4.1 منطق العمليات الأساسية في الواجهة الخلفية

#### 1. نظام إقفال وتسوية الورديات (Shift Control)
قبل السماح للكاشير بإجراء عمليات البيع، يجب أن يستعلم النظام عن وجود وردية مفتوحة للمستخدم الحالي:
```csharp
[HttpGet("check-shift/{userGuid}")]
public async Task<IActionResult> CheckActiveShift(string userGuid)
{
    var activeShift = await _context.PosShifts
        .FirstOrDefaultAsync(s => s.UserGuid == userGuid && s.IsClosed == false);
        
    if (activeShift == null)
    {
        return Ok(new { hasActiveShift = false });
    }
    return Ok(new { hasActiveShift = true, shiftGuid = activeShift.Guid });
}
```

#### 2. الترحيل المحاسبي الآلي للفواتير (Auto-Journal Posting)
عند إجراء أي عملية بيع، يتم استدعاء دالة بناء القيد اليومي آلياً داخل الـ Backend لضمان توازن العمليات المالية في شجرة الحسابات:

```csharp
public async Task PostInvoiceToJournal(Bill invoice, BillTypeConfig config)
{
    var entryGuid = Guid.NewGuid().ToString();
    var journal = new JournalEntry
    {
        Guid = entryGuid,
        EntryTypeCode = invoice.BillTypeCode,
        EntryNumber = invoice.BillNumber,
        EntryDate = invoice.BillDate,
        Note = $"ترحيل الفاتورة رقم {invoice.BillNumber}",
        CurrencyGuid = invoice.CurrencyGuid,
        CurrencyRate = invoice.CurrencyRate
    };

    // 1. الطرف المدين: الصندوق أو حساب العميل
    var debitDetail = new JournalDetail
    {
        ParentGuid = entryGuid,
        AccountGuid = invoice.PaymentType == 0 ? config.CashAccountGuid : invoice.AccountGuid,
        Debit = invoice.TotalFinal,
        Credit = 0.0,
        Note = $"مبيعات فاتورة رقم {invoice.BillNumber}",
        CurrencyGuid = invoice.CurrencyGuid,
        CurrencyRate = invoice.CurrencyRate,
        ValueLocal = invoice.TotalFinal * invoice.CurrencyRate
    };

    // 2. الطرف الدائن: حساب المبيعات (قيمة البضاعة بدون ضريبة)
    var creditDetailSales = new JournalDetail
    {
        ParentGuid = entryGuid,
        AccountGuid = config.SalesAccountGuid,
        Debit = 0.0,
        Credit = invoice.TotalFinal - invoice.TotalVat,
        Note = $"مبيعات فاتورة رقم {invoice.BillNumber}",
        CurrencyGuid = invoice.CurrencyGuid,
        CurrencyRate = invoice.CurrencyRate,
        ValueLocal = (invoice.TotalFinal - invoice.TotalVat) * invoice.CurrencyRate
    };

    // 3. الطرف الدائن الثاني: حساب الضريبة (في حال وجود ضريبة محتسبة)
    JournalDetail creditDetailTax = null;
    if (invoice.TotalVat > 0)
    {
        creditDetailTax = new JournalDetail
        {
            ParentGuid = entryGuid,
            AccountGuid = config.TaxAccountGuid,
            Debit = 0.0,
            Credit = invoice.TotalVat,
            Note = $"ضريبة فاتورة رقم {invoice.BillNumber}",
            CurrencyGuid = invoice.CurrencyGuid,
            CurrencyRate = invoice.CurrencyRate,
            ValueLocal = invoice.TotalVat * invoice.CurrencyRate
        };
    }

    // حفظ القيد وتفاصيله بقاعدة البيانات
    _context.JournalEntries.Add(journal);
    _context.JournalDetails.Add(debitDetail);
    _context.JournalDetails.Add(creditDetailSales);
    if (creditDetailTax != null)
    {
        _context.JournalDetails.Add(creditDetailTax);
    }
    
    await _context.SaveChangesAsync();
}
```

#### 3. التوثيق والمصادقة (JWT Authentication)
بدلاً من نظام تداول كلمات المرور البسيطة القديمة، تعتمد الواجهة الخلفية الحديثة على التحقق من صلاحيات الكاشير والمشرفين باستخدام رموز **JWT (JSON Web Tokens)** المرفقة مع كل طلب شبكي لحماية البيانات الحساسة وصلاحيات العمليات الإدارية (مثل تعديل الأسعار، إلغاء الفواتير، جرد الخزنة).

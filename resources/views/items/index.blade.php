@extends('layouts.app')

@section('title', 'بطاقات المواد')
@section('header_title', 'إدارة المواد والمستودعات')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success"><i class="fas fa-check"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
@endif

<div class="items-container">
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openCreateModal()"><i class="fas fa-plus"></i> إضافة صنف جديد</button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="ابحث بالاسم أو الباركود..." onkeyup="filterTable()">
        </div>
    </div>

    <div class="table-responsive">
        <table id="itemsTable">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>اسم الصنف</th>
                    <th>التصنيف</th>
                    <th>الباركود الأساسي</th>
                    <th>الوحدة الأساسية</th>
                    <th>سعر البيع</th>
                    <th>الضريبة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr class="{{ $item->FREEZ ? 'row-frozen' : '' }}">
                    <td>
                        @if($item->PATH)
                            <img src="{{ asset('storage/' . $item->PATH) }}" class="item-thumbnail" alt="صورة الصنف">
                        @else
                            <div class="no-image"><i class="fas fa-box"></i></div>
                        @endif
                    </td>
                    <td class="item-name">{{ $item->NAME }}</td>
                    <td>{{ $item->category->NAMEAR ?? 'بدون تصنيف' }}</td>
                    <td class="item-barcode">{{ $item->barcode1 ?? '---' }}</td>
                    <td>{{ $item->UNITE1 }}</td>
                    <td>{{ number_format($item->PRICE1, 2) }}</td>
                    <td>{!! $item->CT_PER ? '<span class="badge-success">مفعل ('.$item->PER.'%)</span>' : '<span class="badge-danger">معطل</span>' !!}</td>
                    <td>{!! $item->FREEZ ? '<span class="badge-danger">مجمد</span>' : '<span class="badge-success">نشط</span>' !!}</td>
                    <td>
                        <button class="btn-action btn-edit" onclick="openEditModal({{ json_encode($item) }})" title="تعديل"><i class="fas fa-edit"></i></button>
                        <form action="{{ route('items.destroy', $item->GUID) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف الصنف نهائياً؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" title="حذف"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="itemModal" class="custom-modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة صنف جديد</h3>
            <span class="close-modal" onclick="closeModal('itemModal')">&times;</span>
        </div>
        <form id="itemForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="modal-body">
                <div class="form-tabs">
                    <button type="button" class="ftab-btn active" onclick="showFormTab('basicTab')">البيانات الأساسية</button>
                    <button type="button" class="ftab-btn" onclick="showFormTab('unitsTab')">الوحدات المتعددة والأسعار</button>
                    <button type="button" class="ftab-btn" onclick="showFormTab('settingsTab')">الإعدادات والضرائب</button>
                </div>

                <div id="basicTab" class="ftab-content active">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>اسم الصنف *:</label>
                            <input type="text" name="name" id="inp_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>التصنيف (المجموعة):</label>
                            <select name="category_id" id="inp_category" class="form-control">
                                <option value="">-- بدون تصنيف --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->GUID }}">{{ $cat->NAMEAR }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>صورة الصنف:</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div id="unitsTab" class="ftab-content">
                    <div class="unit-box">
                        <h4>الوحدة الأولى (الصغرى الأساسية كالـ "حبة") *</h4>
                        <div class="grid-4">
                            <div><label>الباركود:</label><input type="text" name="barcode1" id="inp_b1" class="form-control"></div>
                            <div><label>اسم الوحدة:</label><input type="text" name="unit1" id="inp_u1" class="form-control" required value="حبة"></div>
                            <div><label>التكلفة:</label><input type="number" name="cost1" id="inp_c1" class="form-control" step="0.01" value="0"></div>
                            <div><label>سعر البيع *:</label><input type="number" name="price1" id="inp_p1" class="form-control" step="0.01" required></div>
                        </div>
                    </div>

                    <div class="unit-box">
                        <h4>الوحدة الثانية (كالـ "علبة") - اختياري</h4>
                        <div class="grid-5">
                            <div><label>الباركود:</label><input type="text" name="barcode2" id="inp_b2" class="form-control"></div>
                            <div><label>الاسم:</label><input type="text" name="unit2" id="inp_u2" class="form-control" placeholder="علبة"></div>
                            <div><label>معامل التحويل:</label><input type="number" name="qty2" id="inp_q2" class="form-control" step="0.01" placeholder="مثال: 12 حبة"></div>
                            <div><label>التكلفة:</label><input type="number" name="cost2" id="inp_c2" class="form-control" step="0.01"></div>
                            <div><label>البيع:</label><input type="number" name="price2" id="inp_p2" class="form-control" step="0.01"></div>
                        </div>
                    </div>

                    <div class="unit-box">
                        <h4>الوحدة الثالثة (كالـ "كرتون") - اختياري</h4>
                        <div class="grid-5">
                            <div><label>الباركود:</label><input type="text" name="barcode3" id="inp_b3" class="form-control"></div>
                            <div><label>الاسم:</label><input type="text" name="unit3" id="inp_u3" class="form-control" placeholder="كرتون"></div>
                            <div><label>معامل التحويل:</label><input type="number" name="qty3" id="inp_q3" class="form-control" step="0.01" placeholder="مثال: 120 حبة"></div>
                            <div><label>التكلفة:</label><input type="number" name="cost3" id="inp_c3" class="form-control" step="0.01"></div>
                            <div><label>البيع:</label><input type="number" name="price3" id="inp_p3" class="form-control" step="0.01"></div>
                        </div>
                    </div>
                </div>

                <div id="settingsTab" class="ftab-content">
                    <div class="grid-2">
                        <div class="form-group">
                            <label>حد الطلب (ينبهك بنقص الكمية):</label>
                            <input type="number" name="min_order_qty" id="inp_min" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="form-group checkbox-group" style="margin-top: 30px;">
                            <label><input type="checkbox" name="has_expiry_date" id="inp_exp"> الصنف له تاريخ صلاحية</label>
                            <label><input type="checkbox" name="tax_active" id="inp_tax" checked> خاضع للضريبة</label>
                            <label id="freezeContainer" style="display:none; color:red; font-weight:bold;">
                                <input type="checkbox" name="is_frozen" id="inp_freeze"> تجميد الصنف
                            </label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">حفظ بطاقة المادة</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('itemModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('js/items.js') }}"></script>
@endpush

@extends('layouts.app')

@section('title', 'التقارير الشاملة')
@section('header_title', 'مركز التقارير المالية والمخزنية')

@section('content')
<div style="max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
    
    <h2 style="border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 25px;"><i class="fas fa-print"></i> توليد التقارير</h2>

    <form action="{{ route('reports.generate') }}" method="POST" target="_blank">
        @csrf
        
        <div style="margin-bottom: 20px;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">اختر نوع التقرير:</label>
            <select name="report_type" id="report_type" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required onchange="toggleFilters()">
                <option value="account_statement">كشف حساب (عميل / مورد / صندوق)</option>
                <option value="trial_balance">ميزان المراجعة (مطابقة الأرصدة)</option>
                <option value="item_movement">حركة مادة (مبيعات ومشتريات الصنف)</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 8px;">من تاريخ:</label>
                <input type="date" name="from_date" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" value="{{ date('Y-m-01') }}" required>
            </div>
            <div>
                <label style="font-weight: bold; display: block; margin-bottom: 8px;">إلى تاريخ:</label>
                <input type="date" name="to_date" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div id="account_filter" style="margin-bottom: 20px;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">حدد الحساب:</label>
            <select name="account_id" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                @foreach($accounts as $acc)
                    <option value="{{ $acc->GUID }}">{{ $acc->NAME }} ({{ $acc->CODE }})</option>
                @endforeach
            </select>
        </div>

        <div id="item_filter" style="margin-bottom: 20px; display: none;">
            <label style="font-weight: bold; display: block; margin-bottom: 8px;">حدد الصنف:</label>
            <select name="item_id" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                @foreach($items as $item)
                    <option value="{{ $item->GUID }}">{{ $item->NAME }} ({{ $item->barcode1 }})</option>
                @endforeach
            </select>
        </div>

        <div style="text-align: left; margin-top: 30px;">
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-file-pdf"></i> عرض وطباعة التقرير
            </button>
        </div>
    </form>
</div>

<script>
    function toggleFilters() {
        const type = document.getElementById('report_type').value;
        document.getElementById('account_filter').style.display = type === 'account_statement' ? 'block' : 'none';
        document.getElementById('item_filter').style.display = type === 'item_movement' ? 'block' : 'none';
    }
</script>
@endsection
@extends('layouts.app')

@section('title', 'إدارة الورديات والصناديق')
@section('header_title', 'نظام جرد الصناديق والورديات')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shifts.css') }}">
@endpush

@section('content')

@if(session('success'))
    <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
@endif

<div class="shifts-container">
    
    <div class="current-shift-card">
        <h3><i class="fas fa-cash-register"></i> الوردية الحالية</h3>
        
        @if($activeShift)
            <div class="status-box open">الوردية مفتوحة وتعمل الآن</div>
            <div class="shift-details grid-3">
                <div><strong>وقت الفتح:</strong> {{ \Carbon\Carbon::parse($activeShift->DATE)->format('Y-m-d h:i A') }}</div>
                <div><strong>العهدة الافتتاحية:</strong> {{ number_format($activeShift->opening_cash, 2) }}</div>
                <div><strong>المبيعات النقدية للوردية:</strong> <span style="color:#28a745; font-weight:bold;">{{ number_format($currentSales, 2) }}</span></div>
            </div>
            <div class="shift-actions">
                <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> الذهاب لشاشة البيع</a>
                <button class="btn btn-danger" onclick="openCloseModal()"><i class="fas fa-lock"></i> إنهاء الوردية وجرد الصندوق</button>
            </div>
        @else
            <div class="status-box closed">الدرج مغلق. لا توجد وردية نشطة.</div>
            <div class="shift-actions">
                <button class="btn btn-success" onclick="openCreateModal()"><i class="fas fa-unlock"></i> استلام العهدة وفتح الوردية</button>
            </div>
        @endif
    </div>

    <div class="history-section">
        <h3><i class="fas fa-history"></i> سجل الورديات السابقة</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>وقت الفتح</th>
                    <th>وقت الإغلاق</th>
                    <th>العهدة</th>
                    <th>المبلغ المتوقع</th>
                    <th>المبلغ الفعلي (المجرود)</th>
                    <th>العجز / الزيادة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pastShifts as $shift)
                    @php 
                        $diff = $shift->actual_cash - $shift->expected_cash;
                        $diffColor = $diff < 0 ? 'color: red;' : ($diff > 0 ? 'color: green;' : 'color: gray;');
                    @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($shift->DATE)->format('Y-m-d h:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($shift->closed_at)->format('Y-m-d h:i A') }}</td>
                    <td>{{ number_format($shift->opening_cash, 2) }}</td>
                    <td>{{ number_format($shift->expected_cash, 2) }}</td>
                    <td>{{ number_format($shift->actual_cash, 2) }}</td>
                    <td style="{{ $diffColor }} font-weight: bold;">{{ number_format($diff, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;">لا يوجد سجل ورديات سابق.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="createShiftModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>فتح وردية جديدة</h3>
            <span class="close-modal" onclick="closeModal('createShiftModal')">&times;</span>
        </div>
        <form action="{{ route('shifts.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>العهدة الافتتاحية (المبلغ الموجود في الدرج الآن للفكة/الصرف):</label>
                    <input type="number" name="opening_cash" class="form-control" value="0" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">تأكيد وفتح الصندوق</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createShiftModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>

@if($activeShift)
<div id="closeShiftModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="color: #dc3545;">إغلاق الوردية وجرد الصندوق</h3>
            <span class="close-modal" onclick="closeModal('closeShiftModal')">&times;</span>
        </div>
        <form action="{{ route('shifts.close', $activeShift->GUID) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                    <p style="margin: 0; color: #555;">النظام سيقوم بمطابقة هذا المبلغ مع إجمالي المبيعات والعهدة تلقائياً لتسجيل العجز أو الزيادة.</p>
                </div>
                <div class="form-group">
                    <label>المبلغ الفعلي (قم بعدّ المبالغ النقدية الموجودة في الدرج واكتبها هنا):</label>
                    <input type="number" name="actual_cash" class="form-control" style="font-size: 20px; font-weight: bold; color: #0d6efd;" required step="0.01" placeholder="مثال: 1500">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger" onclick="return confirm('تأكيد الجرد وإغلاق الوردية؟ لا يمكن التراجع عن هذه الخطوة.')">تأكيد الإغلاق</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('closeShiftModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function openCreateModal() { document.getElementById('createShiftModal').style.display = 'flex'; }
    function openCloseModal() { document.getElementById('closeShiftModal').style.display = 'flex'; }
    function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
</script>
@endpush
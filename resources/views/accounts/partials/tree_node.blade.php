<li class="tree-node">
    <div class="node-content {{ $account->FREEZ ? 'frozen-account' : '' }}">
        
        @if($account->children->count() > 0)
            <span class="toggle-icon"><i class="fas fa-plus-square"></i></span>
            <i class="fas fa-folder folder-icon"></i>
        @else
            <span class="spacer"></span>
            <i class="fas fa-file-invoice-dollar file-icon"></i>
        @endif
        
        <span class="acc-code">{{ $account->CODE }}</span>
        <span class="acc-name">{{ $account->NAME }} 
            @if($account->TYPE == 0) <small class="badge-main">رئيسي</small> 
            @else <small class="badge-sub">فرعي</small> @endif
            
            @if($account->FREEZ) <small class="badge-frozen">مجمد</small> @endif
        </span>

        <div class="node-actions">
            @if($account->TYPE == 1)
                <a href="#" class="btn-action btn-statement" title="كشف حساب"><i class="fas fa-list-alt"></i> كشف</a>
            @endif

            @if($account->TYPE == 0)
                <button type="button" class="btn-action btn-add" onclick="openCreateModal('{{ $account->GUID }}', '{{ $account->NAME }}')" title="إضافة حساب فرعي"><i class="fas fa-plus"></i> إضافة</button>
            @endif
            
            <button type="button" class="btn-action btn-edit" onclick="openEditModal('{{ $account->GUID }}', '{{ $account->NAME }}', {{ $account->FREEZ ? 'true' : 'false' }})" title="تعديل الحساب"><i class="fas fa-edit"></i> تعديل</button>
            
            <form action="{{ route('accounts.destroy', $account->GUID) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف الحساب؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete" title="حذف الحساب"><i class="fas fa-trash"></i> حذف</button>
            </form>
        </div>
    </div>

    @if($account->children->count() > 0)
        <ul class="nested-tree">
            @foreach($account->children as $child)
                @include('accounts.partials.tree_node', ['account' => $child])
            @endforeach
        </ul>
    @endif
</li>
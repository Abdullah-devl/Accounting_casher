<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $table = 'BILL2';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'PARENT_GUID', 'GUID');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'GUID_ITEM', 'GUID');
    }
}

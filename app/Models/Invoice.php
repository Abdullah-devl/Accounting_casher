<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'BILL1';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'PARENT_GUID', 'GUID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'ACCOUNT', 'GUID');
    }

    public function typeBill()
    {
        return $this->belongsTo(TypeBill::class, 'GUID_BIIL', 'GUID');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'STORE_GUID', 'GUID');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'GUID_JOB', 'GUID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'GUID_CURRENCY', 'GUID');
    }
}

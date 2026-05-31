<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeBill extends Model
{
    protected $table = 'TYPE_BILL';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function salesAccount()
    {
        return $this->belongsTo(Account::class, 'day_item', 'GUID');
    }

    public function discountAccount()
    {
        return $this->belongsTo(Account::class, 'day_disc', 'GUID');
    }

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'cash_day', 'GUID');
    }

    public function taxAccount()
    {
        return $this->belongsTo(Account::class, 'cash_vat', 'GUID');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'GUID_STORE', 'GUID');
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

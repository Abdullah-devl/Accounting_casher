<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReceipt extends Model
{
    protected $table = 'CASH_DAY';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class, 'GUID_ACCOUNT', 'GUID');
    }

    public function customer()
    {
        return $this->belongsTo(Account::class, 'GUID_CUSTOMER', 'GUID');
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

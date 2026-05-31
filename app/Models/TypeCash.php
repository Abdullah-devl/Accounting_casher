<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeCash extends Model
{
    protected $table = 'TYPE_CASH';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function job()
    {
        return $this->belongsTo(Job::class, 'GUID_JOB', 'GUID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'GUID_CURRENCY', 'GUID');
    }

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'DAY_CASH', 'GUID');
    }
}

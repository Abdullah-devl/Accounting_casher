<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'ACCOUNT';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'PARENT_GUID', 'GUID');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'PARENT_GUID', 'GUID');
    }

    public function journalDetails()
    {
        return $this->hasMany(JournalDetail::class, 'ACCOUNT_GUID', 'GUID');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'GUID_CURRENCY', 'GUID');
    }
}

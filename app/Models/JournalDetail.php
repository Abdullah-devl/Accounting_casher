<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
    protected $table = 'DAY2';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'PARENT_GUID', 'GUID');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'ACCOUNT_GUID', 'GUID');
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

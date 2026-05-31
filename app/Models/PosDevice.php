<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosDevice extends Model
{
    protected $table = 'TB_POSES';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'GUID_USER', 'GUID');
    }

    public function saleType()
    {
        return $this->belongsTo(TypeBill::class, 'GUID_SALE', 'GUID');
    }

    public function returnType()
    {
        return $this->belongsTo(TypeBill::class, 'GUID_RSALE', 'GUID');
    }

    public function cashAccount()
    {
        return $this->belongsTo(Account::class, 'ACCOUNT_CASH', 'GUID');
    }
}

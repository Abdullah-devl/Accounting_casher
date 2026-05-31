<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $table = 'TB_POSI';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(Item::class, 'GUIDI', 'GUID');
    }
}

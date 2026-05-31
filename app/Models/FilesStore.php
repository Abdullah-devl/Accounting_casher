<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilesStore extends Model
{
    protected $table = 'FILES_STORE';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
}

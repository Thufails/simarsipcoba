<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $table = 'operator';
    protected $primaryKey = 'ID_OPERATOR';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_OPERATOR',
        'EMAIL',
        'PASSWORD'
    ];

    public function HakAkses()
    {
        return $this->belongsTo(HakAkses::class, 'ID_AKSES');
    }
}

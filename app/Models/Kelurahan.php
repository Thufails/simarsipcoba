<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'ID_KELURAHAN';
    public $timestamps = true;

    protected $fillable = [
        'ID_KECAMATAN',
        'NAMA_KELURAHAN'
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'ID_KECAMATAN');
    }

    public function InfoArsipSuratPindah()
    {
        return $this->hasMany(InfoArsipSuratPindah::class, 'ID_KECAMATAN');
    }

    public function InfoArsipSkot()
    {
        return $this->hasMany(InfoArsipSkot::class, 'ID_KECAMATAN');
    }

    public function InfoArsipSktt()
    {
        return $this->hasMany(InfoArsipSktt::class, 'ID_KECAMATAN');
    }
    
    public function InfoArsipKtp()
    {
        return $this->hasMany(InfoArsipKtp::class, 'ID_KECAMATAN');
    }

}

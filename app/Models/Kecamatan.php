<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'ID_KECAMATAN';
    public $timestamps = true;

    protected $fillable = [
        'NAMA_KECAMATAN'
    ];

    public function Kelurahan()
    {
        return $this->hasMany(Kelurahan::class,'ID_KELURANAN');
    }

    public function InfoArsipSuratPindah()
    {
        return $this->hasMany(InfoArsipSuratPindah::class,'ID_KECAMATAN');
    }

    public function InfoArsipKk()
    {
        return $this->hasMany(InfoArsipKk::class, 'ID_KECAMATAN');
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

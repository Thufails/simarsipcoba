<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('info_arsip_ktp', function (Blueprint $table) {
            $table->string('NO_DOK_KTP')->primary();
            $table->string('NAMA', 50);
            $table->string('JENIS_KELAMIN', 15);
            $table->string('TEMPAT_LAHIR', 25);
            $table->date('TANGGAL_LAHIR');
            $table->string('AGAMA', 15);
            $table->string('STATUS_KAWIN', 15);
            $table->string('KEBANGSAAN', 15);
            $table->string('NO_PASPOR', 25);
            $table->string('HUB_KELUARGA', 25);
            $table->string('PEKERJAAN', 25);
            $table->string('GOLDAR', 10);
            $table->string('ALAMAT', 50);
            $table->string('PROV', 50);
            $table->string('KOTA', 50);
            $table->bigInteger('TAHUN_PEMBUATAN_KTP');
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_KUTIPAN_KTP')->nullable();
            $table->longText('FILE_SK_HILANG')->nullable();
            $table->longText('FILE_AKTA_LAHIR')->nullable();
            $table->longText('FILE_IJAZAH')->nullable();
            $table->longText('FILE_SURAT_NIKAH_CERAI')->nullable();
            $table->longText('FILE_SURAT_PINDAH')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_KTP')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
            $table->foreignId('ID_KELURAHAN')->nullable();
            $table->foreignId('ID_KECAMATAN')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_ktp');
    }
};

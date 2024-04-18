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
        Schema::create('info_arsip_kelahiran', function (Blueprint $table) {
            $table->string('NO_DOK_KELAHIRAN', 25)->primary();
            $table->string('NAMA', 50);
            $table->string('TEMPAT_LAHIR', 25);
            $table->date('TANGGAL_LAHIR');
            $table->integer('ANAK_KE');
            $table->string('NAMA_AYAH', 50);
            $table->string('NAMA_IBU', 50);
            $table->bigInteger('NO_KK');
            $table->date('TAHUN_PEMBUATAN_DOK_KELAHIRAN');
            $table->string('STATUS_KELAHIRAN', 25);
            $table->string('STATUS_PENDUDUK', 25);
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_KK')->nullable();
            $table->longText('FILE_KTP_AYAH')->nullable();
            $table->longText('FILE_KTP_IBU')->nullable();
            $table->longText('FILE_F102')->nullable();
            $table->longText('FILE_F201')->nullable();
            $table->longText('FILE_BUKU_NIKAH')->nullable();
            $table->longText('FILE_KUTIPAN_KELAHIRAN')->nullable();
            $table->longText('FILE_SURAT_KELAHIRAN')->nullable();
            $table->longText('FILE_SPTJM_PENERBITAN')->nullable();
            $table->longText('FILE_PELAPORAN_KELAHIRAN')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_AKTA_KELAHIRAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_kelahiran');
    }
};

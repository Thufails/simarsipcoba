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
        Schema::create('info_arsip_skot', function (Blueprint $table) {
            $table->string('NO_DOK_SKOT', 25)->primary();
            $table->string('NAMA', 50);
            $table->string('NAMA_PANGGIL', 25);
            $table->bigInteger('NIK');
            $table->string('JENIS_KELAMIN', 15);
            $table->string('TEMPAT_LAHIR', 25);
            $table->date('TANGGAL_LAHIR');
            $table->string('AGAMA', 15);
            $table->string('STATUS_KAWIN', 15);
            $table->string('PEKERJAAN', 25);
            $table->string('ALAMAT_ASAL', 50);
            $table->string('PROV_ASAL', 25);
            $table->string('KOTA_ASAL', 25);
            $table->string('KEC_ASAL', 25);
            $table->string('KEL_ASAL', 25);
            $table->string('ALAMAT', 50);
            $table->string('PROV', 50);
            $table->string('KOTA', 50);
            $table->date('TAHUN_PEMBUATAN_DOK_SKOT');
            $table->longText('FILE_LAMA')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_SKOT')->nullable();
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
        Schema::dropIfExists('info_arsip_skot');
    }
};

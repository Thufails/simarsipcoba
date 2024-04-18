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
        Schema::create('info_arsip_sktt', function (Blueprint $table) {
            $table->string('NO_DOK_SKTT', 25)->primary();
            $table->string('NAMA', 50);
            $table->string('JENIS_KELAMIN', 15);
            $table->string('TEMPAT_LAHIR', 25);
            $table->date('TANGGAL_LAHIR');
            $table->string('AGAMA', 15);
            $table->string('STATUS_KAWIN', 15);
            $table->string('KEBANGSAAN', 15);
            $table->string('NO_PASPOR', 25)->nullable();
            $table->string('HUB_KELUARGA', 25);
            $table->string('PEKERJAAN', 25);
            $table->string('GOLDAR', 10);
            $table->string('ALAMAT', 50);
            $table->string('PROV', 50);
            $table->string('KOTA', 50);
            $table->date('TAHUN_PEMBUATAN_DOK_SKTT');
            $table->longText('FILE_LAMA')->nullable();
            $table->text('FILE_LAINNYA')->nullable();
            $table->text('FILE_SKTT')->nullable();
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
        Schema::dropIfExists('info_arsip_sktt');
    }
};

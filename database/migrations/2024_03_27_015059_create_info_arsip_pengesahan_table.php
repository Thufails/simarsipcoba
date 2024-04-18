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
        Schema::create('info_arsip_pengesahan', function (Blueprint $table) {
            $table->string('NO_DOK_PENGESAHAN', 25)->primary();
            $table->string('NAMA_ANAK', 50);
            $table->date('TANGGAL_LAHIR');
            $table->string('TEMPAT_LAHIR', 25);
            $table->string('JENIS_KELAMIN', 15);
            $table->string('NO_AKTA_KELAHIRAN', 50);
            $table->string('NO_PP', 25);
            $table->date('TANGGAL_PP');
            $table->string('NAMA_AYAH', 50);
            $table->string('NAMA_IBU', 50);
            $table->date('TAHUN_PEMBUATAN_DOK_PENGESAHAN');
            $table->longText('FILE_LAMA')->nullable();
            $table->longText('FILE_LAINNYA')->nullable();
            $table->longText('FILE_PENGESAHAN')->nullable();
            $table->timestamps();

            $table->foreignId('ID_ARSIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_arsip_pengesahan');
    }
};

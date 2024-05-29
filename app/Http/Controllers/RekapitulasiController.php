<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\HistoryPelayanan;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\JenisDokumen;
use App\Models\Kecamatan;
use App\Models\Kelurahan;

class RekapitulasiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function filterBaseKecamatan (Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'ID_KECAMATAN' => 'nullable|exists:kecamatan,ID_KECAMATAN',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $namaKecamatan = Kecamatan::where('ID_KECAMATAN', $request->ID_KECAMATAN)->value('NAMA_KECAMATAN');
        $query = Arsip::with('jenisDokumen');

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }
        $query->where(function ($q) use ($request) {
            $q->whereHas('infoArsipSuratPindah', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipKk', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipSkot', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipSktt', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            })->orWhereHas('infoArsipKtp', function ($query) use ($request) {
                $query->where('ID_KECAMATAN', $request->ID_KECAMATAN);
            });
        });

        $arsips = $query->get();

        $formattedArsips = $arsips->map(function ($arsip) {
            $models = [
                'infoArsipSuratPindah' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_SKP_WNI', 'FILE_KTP_ASAL', 'FILE_NIKAH_CERAI', 'FILE_AKTA_KELAHIRAN', 'FILE_KK', 'FILE_F101', 'FILE_102', 'FILE_DOK_PENDUKUNG', 'FILE_LAINNYA', 'FILE_SURAT_PINDAH'],
                'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_F101', 'FILE_NIKAH_CERAI', 'FILE_SK_PINDAH', 'FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI', 'FILE_PUTUSAN_PRESIDEN', 'FILE_KK_LAMA', 'FILE_SK_PERISTIWA', 'FILE_SK_HILANG', 'FILE_KTP', 'FILE_LAINNYA', 'FILE_KK'],
                'infoArsipSkot' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                'infoArsipSktt' => ['NAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                'infoArsipKtp' => ['NAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR', 'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
            ];

            foreach ($models as $relation => $columns) {
                if (is_array($columns)) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column)) {
                            if (strpos($column, 'NAMA'&'NAMA_') !== false) {
                                $NAMA[] = $arsip->$relation->$column;
                            } elseif (strpos($column, 'FILE_') !== false) {
                                $DOKUMEN[] = $arsip->$relation->$column;
                            }
                        }
                    }
                } else {
                    if (!empty($arsip->$relation->$columns)) {
                        if (strpos($columns, 'NAMA'&'NAMA_') !== false) {
                            $NAMA[] = $arsip->$relation->$columns;
                        } elseif (strpos($columns, 'FILE_') !== false) {
                            $DOKUMEN[] = $arsip->$relation->$columns;
                        }
                    }
                }
            }

            $formattedDokumen = [];
            foreach ($models as $relation => $columns) {
                foreach ($columns as $column) {
                    if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                        $formattedDokumen[$column] = $arsip->$relation->$column;
                    }
                }
            }

            return [
                'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOKUMEN' => implode(', ', array_filter([
                        $arsip->NO_DOK_PENGANGKATAN,
                        $arsip->NO_DOK_SURAT_PINDAH,
                        $arsip->NO_DOK_PERCERAIAN,
                        $arsip->NO_DOK_PENGESAHAN,
                        $arsip->NO_DOK_KEMATIAN,
                        $arsip->NO_DOK_KELAHIRAN,
                        $arsip->NO_DOK_PENGAKUAN,
                        $arsip->NO_DOK_PERKAWINAN,
                        $arsip->NO_DOK_KK,
                        $arsip->NO_DOK_SKOT,
                        $arsip->NO_DOK_SKTT,
                        $arsip->NO_DOK_KTP,
                    ])),
                    'NAMA' => implode(', ', $NAMA),
                    'DOKUMEN' => $formattedDokumen,
                    'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                    'NO_BUKU' => $arsip->NO_BUKU,
                    'NO_RAK' => $arsip->NO_RAK,
                    'NO_BARIS' => $arsip->NO_BARIS,
                    'NO_BOKS' => $arsip->NO_BOKS,
                    'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                    'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                    'KETERANGAN' => $arsip->KETERANGAN,
            ];
        });

        // Mengembalikan data dokumen dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Sukses Menampilkan Arsip by Kecamatan: ' . $namaKecamatan,
            'dokumen' => $formattedArsips,
        ], 200);
    }

    public function filterBaseKelurahan (Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'JENIS_DOKUMEN' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'ID_KELURAHAN' => 'nullable|exists:kelurahan,ID_KELURAHAN',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        $namaKelurahan = Kelurahan::where('ID_KELURAHAN', $request->ID_KELURAHAN)->value('NAMA_KELURAHAN');
        $query = Arsip::with('jenisDokumen');

        if ($request->has('JENIS_DOKUMEN') && $request->JENIS_DOKUMEN != null) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('ID_DOKUMEN', $request->JENIS_DOKUMEN);
            });
        }
        $query->where(function ($q) use ($request) {
            $q->whereHas('infoArsipSuratPindah', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipKk', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipSkot', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipSktt', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            })->orWhereHas('infoArsipKtp', function ($query) use ($request) {
                $query->where('ID_KELURAHAN', $request->ID_KELURAHAN);
            });
        });

        $arsips = $query->get();

        $formattedArsips = $arsips->map(function ($arsip) {
            $models = [
                'infoArsipSuratPindah' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_SKP_WNI', 'FILE_KTP_ASAL', 'FILE_NIKAH_CERAI', 'FILE_AKTA_KELAHIRAN', 'FILE_KK', 'FILE_F101', 'FILE_102', 'FILE_DOK_PENDUKUNG', 'FILE_LAINNYA', 'FILE_SURAT_PINDAH'],
                'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA', 'FILE_F101', 'FILE_NIKAH_CERAI', 'FILE_SK_PINDAH', 'FILE_SK_PINDAH_LUAR', 'FILE_SK_PENGGANTI', 'FILE_PUTUSAN_PRESIDEN', 'FILE_KK_LAMA', 'FILE_SK_PERISTIWA', 'FILE_SK_HILANG', 'FILE_KTP', 'FILE_LAINNYA', 'FILE_KK'],
                'infoArsipSkot' => ['NAMA', 'FILE_LAMA', 'FILE_LAINNYA', 'FILE_SKOT'],
                'infoArsipSktt' => ['NAMA', 'FILE_LAINNYA', 'FILE_SKTT'],
                'infoArsipKtp' => ['NAMA', 'FILE_KK', 'FILE_KUTIPAN_KTP', 'FILE_SK_HILANG', 'FILE_AKTA_LAHIR', 'FILE_IJAZAH', 'FILE_SURAT_NIKAH_CERAI', 'FILE_SURAT_PINDAH', 'FILE_LAINNYA', 'FILE_KTP'],
            ];

            foreach ($models as $relation => $columns) {
                if (is_array($columns)) {
                    foreach ($columns as $column) {
                        if (!empty($arsip->$relation->$column)) {
                            if (strpos($column, 'NAMA'&'NAMA_') !== false) {
                                $NAMA[] = $arsip->$relation->$column;
                            } elseif (strpos($column, 'FILE_') !== false) {
                                $DOKUMEN[] = $arsip->$relation->$column;
                            }
                        }
                    }
                } else {
                    if (!empty($arsip->$relation->$columns)) {
                        if (strpos($columns, 'NAMA'&'NAMA_') !== false) {
                            $NAMA[] = $arsip->$relation->$columns;
                        } elseif (strpos($columns, 'FILE_') !== false) {
                            $DOKUMEN[] = $arsip->$relation->$columns;
                        }
                    }
                }
            }

            $formattedDokumen = [];
            foreach ($models as $relation => $columns) {
                foreach ($columns as $column) {
                    if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                        $formattedDokumen[$column] = $arsip->$relation->$column;
                    }
                }
            }

            return [
                'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOKUMEN' => implode(', ', array_filter([
                        $arsip->NO_DOK_PENGANGKATAN,
                        $arsip->NO_DOK_SURAT_PINDAH,
                        $arsip->NO_DOK_PERCERAIAN,
                        $arsip->NO_DOK_PENGESAHAN,
                        $arsip->NO_DOK_KEMATIAN,
                        $arsip->NO_DOK_KELAHIRAN,
                        $arsip->NO_DOK_PENGAKUAN,
                        $arsip->NO_DOK_PERKAWINAN,
                        $arsip->NO_DOK_KK,
                        $arsip->NO_DOK_SKOT,
                        $arsip->NO_DOK_SKTT,
                        $arsip->NO_DOK_KTP,
                    ])),
                    'NAMA' => implode(', ', $NAMA),
                    'DOKUMEN' => $formattedDokumen,
                    'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                    'NO_BUKU' => $arsip->NO_BUKU,
                    'NO_RAK' => $arsip->NO_RAK,
                    'NO_BARIS' => $arsip->NO_BARIS,
                    'NO_BOKS' => $arsip->NO_BOKS,
                    'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                    'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                    'KETERANGAN' => $arsip->KETERANGAN,
            ];
        });

        // Mengembalikan data dokumen dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Sukses Menampilkan Arsip by Kelurahan: ' . $namaKelurahan,
            'dokumen' => $formattedArsips,
        ], 200);
    }

    public function filterBaseTahun (Request $request)
    {

    }

    public function filterBaseJenis (Request $request)
    {

    }


}





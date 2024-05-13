<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\HakAkses;
use App\Models\Operator;
use App\Models\Permission;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ManajemenController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api',['except'=>['login', 'register']]);
    }

    public function getArsipById($ID_PERMISSION)
    {
        $permissionRequest = Permission::find($ID_PERMISSION);

        // Jika permintaan tidak ditemukan, kembalikan respons dengan status 404
        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin tidak ditemukan',
            ], 404);
        }
        $ID_ARSIP = $permissionRequest->ID_ARSIP;
        // Cari arsip berdasarkan ID
        $arsip = Arsip::with('jenisDokumen')
                      ->with([
                          'infoArsipPengangkatan',
                          'infoArsipSuratPindah',
                          'infoArsipPerceraian',
                          'infoArsipPengesahan',
                          'infoArsipKematian',
                          'infoArsipKelahiran',
                          'infoArsipPengakuan',
                          'infoArsipPerkawinan',
                          'infoArsipKk',
                          'infoArsipSkot',
                          'infoArsipSktt',
                          'infoArsipKtp'
                      ])
                      ->find($ID_ARSIP);

        // Jika arsip ditemukan
        if ($arsip) {
            // Format data sesuai kebutuhan
            $formattedArsip = [
                'ID_PERMISSION' => $permissionRequest->ID_PERMISSION,
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
                'NAMA' => '',
                'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                'NO_BUKU' => $arsip->NO_BUKU,
                'NO_RAK' => $arsip->NO_RAK,
                'NO_BARIS' => $arsip->NO_BARIS,
                'NO_BOKS' => $arsip->NO_BOKS,
                'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                'KETERANGAN' => $arsip->KETERANGAN,


                // Tambahkan kolom lain sesuai kebutuhan
            ];

            $models = [
                'infoArsipPengangkatan',
                'infoArsipSuratPindah',
                'infoArsipPerceraian',
                'infoArsipPengesahan',
                'infoArsipKematian',
                'infoArsipKelahiran',
                'infoArsipPengakuan',
                'infoArsipPerkawinan',
                'infoArsipKk',
                'infoArsipSkot',
                'infoArsipSktt',
                'infoArsipKtp'
            ];

            foreach ($models as $relation) {
                if ($arsip->$relation) {
                    // Menggabungkan data ke dalam $formattedArsip tanpa membungkusnya dalam array
                    $formattedArsip['INFO_ARSIP'] = $arsip->$relation;
                    break; // Berhenti setelah menemukan data yang ada
                }
            }


            // Mendapatkan NAMA dan dokumen dari setiap tabel terkait
            $NAMA = [];
            $models = [
                'infoArsipPengangkatan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGANGKATAN'],
                'infoArsipSuratPindah' => ['NAMA_KEPALA','FILE_LAMA','FILE_SKP_WNI','FILE_KTP_ASAL','FILE_NIKAH_CERAI',
                                            'FILE_AKTA_KELAHIRAN','FILE_KK','FILE_F101','FILE_102','FILE_DOK_PENDUKUNG',
                                            'FILE_LAINNYA','FILE_SURAT_PINDAH'],
                'infoArsipPerceraian' => ['NAMA_PRIA', 'NAMA_WANITA','FILE_LAMA','FILE_F201','FILE_FC_PP',
                                            'FILE_KUTIPAN_PERKAWINAN','FILE_KTP','FILE_KK','FILE_SPTJM','FILE_LAINNYA',
                                            'FILE_AKTA_PERCERAIAN','FILE_AKTA_PERKAWINAN'],
                'infoArsipPengesahan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGESAHAN'],
                'infoArsipKematian' => ['NAMA', 'FILE_LAMA','FILE_F201','FILE_SK_KEMATIAN','FILE_KK','FILE_KTP',
                                        'FILE_KTP_SUAMI_ISTRI','FILE_KUTIPAN_KEMATIAN','FILE_FC_PP','FILE_FC_DOK_PERJALANAN',
                                        'FILE_DOK_PENDUKUNG','FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_KEMATIAN'],
                'infoArsipKelahiran' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KTP_AYAH','FILE_KTP_IBU','FILE_F102','FILE_F201',
                                        'FILE_BUKU_NIKAH','FILE_KUTIPAN_KELAHIRAN','FILE_SURAT_KELAHIRAN','FILE_SPTJM_PENERBITAN',
                                        'FILE_PELAPORAN_KELAHIRAN','FILE_LAINNYA','FILE_AKTA_KELAHIRAN'],
                'infoArsipPengakuan' => ['NAMA_ANAK', 'FILE_LAMA','FILE_LAINNYA','FILE_PENGAKUAN'],
                'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA','FILE_LAMA','FILE_LAINNYA','FILE_F201','FILE_FC_SK_KAWIN',
                                            'FILE_FC_PASFOTO','FILE_KTP','FILE_KK','FILE_AKTA_KEMATIAN','FILE_AKTA_PERCERAIAN',
                                            'FILE_SPTJM','FILE_LAINNYA','FILE_AKTA_PERKAWINAN'],
                'infoArsipKk' => ['NAMA_KEPALA', 'FILE_LAMA','FILE_F101','FILE_NIKAH_CERAI','FILE_SK_PINDAH','FILE_SK_PINDAH_LUAR',
                                    'FILE_SK_PENGGANTI','FILE_PUTUSAN_PRESIDEN','FILE_KK_LAMA','FILE_SK_PERISTIWA','FILE_SK_HILANG',
                                    'FILE_KTP','FILE_LAINNYA','FILE_KK'],
                'infoArsipSkot' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],
                'infoArsipSktt' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],
                'infoArsipKtp' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                    'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
            ];


            // Mendapatkan NAMA dan dokumen dari setiap tabel terkait
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

            // Gabungkan NAMA dan dokumen menjadi satu string
            $formattedArsip['NAMA'] = implode(', ', $NAMA);

            $formattedDokumen = [];
            foreach ($models as $relation => $columns) {
                foreach ($columns as $column) {
                    if (!empty($arsip->$relation->$column) && strpos($column, 'FILE_') !== false) {
                        $formattedDokumen[$column] = $arsip->$relation->$column;
                    }
                }
            }

            $formattedArsip['DOKUMEN'] = $formattedDokumen;


            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Data Arsip',
                'arsip' => $formattedArsip
            ], 200);
        } else {
            // Jika arsip tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
                'arsip' => null
            ], 404);
        }
    }

}

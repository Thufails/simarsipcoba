<?php

namespace App\Http\Controllers;

use App\Models\InfoArsipKematian;
use App\Models\User;
use App\Models\Arsip;
use App\Models\InfoArsipKelahiran;
use App\Models\InfoArsipKk;
use App\Models\InfoArsipKtp;
use App\Models\InfoArsipPengakuan;
use App\Models\InfoArsipPengangkatan;
use App\Models\InfoArsipPengesahan;
use App\Models\InfoArsipPerceraian;
use App\Models\InfoArsipPerkawinan;
use App\Models\InfoArsipSkot;
use App\Models\InfoArsipSktt;
use App\Models\InfoArsipSuratPindah;
use App\Models\JenisDokumen;
use App\Models\Permission;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
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

    public function getPermission(Request $request)
{
    $permissions = Permission::with('Operator')->get();

    // Loop melalui setiap izin dan cari yang sesuai dengan ID_ARSIP yang diberikan
    $arsipIds = $permissions->pluck('ID_ARSIP')->toArray();
    $arsips = Arsip::with('jenisDokumen')
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
                   ])->whereIn('ID_ARSIP', $arsipIds) // Ambil data Arsip dengan ID_ARSIP yang sesuai
                   ->get();

    // Mengembalikan data dalam format JSON
    if ($arsips->isNotEmpty()) {
        // Format data sesuai kebutuhan
        $formattedPermissions = $permissions->map(function ($permission) use ($arsips) {
            // Filter arsip yang sesuai dengan ID_ARSIP pada izin saat ini
            $arsip = $arsips->where('ID_ARSIP', $permission->ID_ARSIP)->first();

            // Format data izin
            $formattedPermission = [
                'ID_PERMISSION' => $permission->ID_PERMISSION,
                'STATUS' => $permission->STATUS,
                'created_at' => $permission->created_at,
                'updated_at' => $permission->updated_at,
                'ID_ARSIP' => $permission->ID_ARSIP,
                'ID_OPERATOR' => $permission->ID_OPERATOR,
                'NAMA_OPERATOR' => optional($permission->operator)->NAMA_OPERATOR
                // 'arsips' => $formattedArsip, // Inisialisasi array untuk data arsip
            ];

            // Jika arsip ditemukan, tambahkan data arsip ke dalam izin
            if ($arsip) {
                $NAMA = [];
                $DOKUMEN = [];

                // Mendapatkan NAMA dan dokumen dari setiap tabel terkait
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
                    'infoArsipSkot' => ['NAMA', 'NAMA_PANGGIL', 'FILE_LAMA','FILE_LAINNYA','FILE_SKOT'],
                    'infoArsipSktt' => ['NAMA', 'FILE_LAMA','FILE_LAINNYA','FILE_SKTT'],
                    'infoArsipKtp' => ['NAMA', 'FILE_LAMA','FILE_KK','FILE_KUTIPAN_KTP','FILE_SK_HILANG','FILE_AKTA_LAHIR',
                                            'FILE_IJAZAH','FILE_SURAT_NIKAH_CERAI','FILE_SURAT_PINDAH','FILE_LAINNYA','FILE_KTP'],
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

                $NAMA = implode(', ', $NAMA);

                $formattedArsip = [
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
                    'NAMA' => $NAMA,
                    'DOKUMEN' => $DOKUMEN,
                    'JUMLAH_BERKAS' => $arsip->JUMLAH_BERKAS,
                    'NO_BUKU' => $arsip->NO_BUKU,
                    'NO_RAK' => $arsip->NO_RAK,
                    'NO_BARIS' => $arsip->NO_BARIS,
                    'NO_BOKS' => $arsip->NO_BOKS,
                    'LOK_SIMPAN' => $arsip->LOK_SIMPAN,
                    'TANGGAL_PINDAI' => $arsip->TANGGAL_PINDAI,
                    'KETERANGAN' => $arsip->KETERANGAN,
                ];

                $formattedPermission['ARSIP'] = $formattedArsip;
            }

            return $formattedPermission;
        });

        return response()->json([
            'success' => true,
            'message' => 'Sukses Menampilkan Data Permission',
            'permissions' => $formattedPermissions
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada data Permission',
            'data' => []
        ], 404);
    }
}

    public function requestPermission(Request $request, $ID_ARSIP)
    {
        // Mendapatkan ID pengguna yang meminta akses
        $userRequestingId = Auth::id(); // Menggunakan id() untuk mendapatkan langsung ID

        // Mencari dokumen berdasarkan ID_ARSIP
        $document = Arsip::with('jenisDokumen')
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
                      ])->find($ID_ARSIP);

        // Validasi apakah dokumen ditemukan
        if (!$document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan'], 404);
        }

        $nama= [];
        // Mendefinisikan array asosiasi model dengan nama kolom yang sesuai
        $models = [
            'infoArsipPengangkatan' => ['NAMA_ANAK'],
            'infoArsipSuratPindah' => ['NAMA_KEPALA'],
            'infoArsipPerceraian' => ['NAMA_PRIA'],
            'infoArsipPengesahan' => ['NAMA_ANAK'],
            'infoArsipKematian' => ['NAMA'],
            'infoArsipKelahiran' => ['NAMA'],
            'infoArsipPengakuan' => ['NAMA_ANAK'],
            'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA'],
            'infoArsipKk' => ['NAMA_KEPALA'],
            'infoArsipSkot' => ['NAMA'],
            'infoArsipSktt' => ['NAMA'],
            'infoArsipKtp' => ['NAMA'],
        ];

        // Periksa ID_ARSIP dan tentukan model yang sesuai
        foreach ($models as $relation => $columns) {
            // Cek apakah relasi tersedia dan setidaknya satu dokumen tidak kosong
            if ($document->$relation) {
                foreach ($columns as $column) {
                    if (!empty($document->$relation->$column)) {
                        // Tambahkan dokumen ke dalam array
                        $nama[] = $document->$relation->$column;
                    }
                }
            }
        }
        // Proses permintaan ijin
        $permissionRequest = new Permission();
        $permissionRequest->ID_OPERATOR = $userRequestingId;
        $permissionRequest->ID_ARSIP = $document->ID_ARSIP;
        $permissionRequest->STATUS = 'Request Lihat';
        $permissionRequest->save();

        if ($permissionRequest) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan ijin berhasil diajukan. Menunggu persetujuan Arsiparis.',
                'nama_pemilik' => $nama,
                'data' => $permissionRequest,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin gagal diajukan.',
                'data' => ''
            ], 400);
        }
    }

    public function requestScan(Request $request, $ID_ARSIP)
    {
        $userRequestingId = Auth::id(); // ID pengguna yang meminta akses

            $document = Arsip::with('jenisDokumen')
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
                      ])->find($ID_ARSIP);

        // Validasi apakah dokumen ditemukan
        if (!$document) {
            return response()->json(['message' => 'Dokumen tidak ditemukan'], 404);
        }

        $nama= [];
        // Mendefinisikan array asosiasi model dengan nama kolom yang sesuai
        $models = [
            'infoArsipPengangkatan' => ['NAMA_ANAK'],
            'infoArsipSuratPindah' => ['NAMA_KEPALA'],
            'infoArsipPerceraian' => ['NAMA_PRIA'],
            'infoArsipPengesahan' => ['NAMA_ANAK'],
            'infoArsipKematian' => ['NAMA'],
            'infoArsipKelahiran' => ['NAMA'],
            'infoArsipPengakuan' => ['NAMA_ANAK'],
            'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA'],
            'infoArsipKk' => ['NAMA_KEPALA'],
            'infoArsipSkot' => ['NAMA'],
            'infoArsipSktt' => ['NAMA'],
            'infoArsipKtp' => ['NAMA'],
        ];

        // Periksa ID_ARSIP dan tentukan model yang sesuai
        foreach ($models as $relation => $columns) {
            // Cek apakah relasi tersedia dan setidaknya satu dokumen tidak kosong
            if ($document->$relation) {
                foreach ($columns as $column) {
                    if (!empty($document->$relation->$column)) {
                        // Tambahkan dokumen ke dalam array
                        $nama[] = $document->$relation->$column;
                    }
                }
            }
        }

        // Proses permintaan Scan
        $permissionRequest = new Permission();
        $permissionRequest->ID_OPERATOR = $userRequestingId;
        $permissionRequest->ID_ARSIP = $document->ID_ARSIP;
        $permissionRequest->STATUS = 'Request Scan';
        $permissionRequest->save();

        if ($permissionRequest) {
            return response()->json([
                'success' => true,
                'message' => 'Permintaan Scan berhasil diajukan. Menunggu Arsiparis.',
                'nama_pemilik' => $nama,
                'data' => $permissionRequest,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan Scan gagal diajukan.',
                'data' => ''
            ], 400);
        }
    }


    public function approvePermission(Request $request, $ID_PERMISSION)
    {
        $permissionRequest = Permission::find($ID_PERMISSION);

        // Jika permintaan tidak ditemukan, kembalikan respons dengan status 404
        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin tidak ditemukan',
            ], 404);
        }

        // Perbarui status permintaan menjadi "DISETUJUI"
        $permissionRequest->update(['STATUS' => 'Disetujui']);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan ijin telah disetujui',
        ], 200);

    }

    public function scanDokumen( Request $request,$ID_PERMISSION, $ID_ARSIP )
    {
        // Cari arsip berdasarkan ID_ARSIP dengan informasi jenis dokumen terkait
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
                      ])->find($ID_ARSIP);

        // Jika arsip tidak ditemukan, kembalikan respons dengan status 404
        if (!$arsip) {
            return response()->json([
                'success' => false,
                'message' => 'Arsip tidak ditemukan',
            ], 404);
        }

        $jenisDokumen = $arsip->jenisDokumen->NAMA_DOKUMEN;

        // Lakukan proses upload dokumen sesuai dengan jenis dokumen
        switch ($jenisDokumen) {
            case 'Akta Pengangkatan Anak':
                $infoArsipPengangkatan = InfoArsipPengangkatan::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokPengangkatan = $infoArsipPengangkatan->THN_PEMBUATAN_DOK_PENGANGKATAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_PENGANGKATAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokPengangkatan . '/Arsip Pengangkatan';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipPengangkatan->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipPengangkatan->save();
                break;
            case 'Surat Pindah':
                $infoArsipSuratPindah = InfoArsipSuratPindah::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokSuratPindah= $infoArsipSuratPindah->THN_PEMBUATAN_DOK_SURAT_PINDAH;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_SKP_WNI',
                    'FILE_KTP_ASAL',
                    'FILE_NIKAH_CERAI',
                    'FILE_AKTA_KELAHIRAN',
                    'FILE_KK',
                    'FILE_F101',
                    'FILE_F102',
                    'FILE_F103',
                    'FILE_DOK_PENDUKUNG',
                    'FILE_LAINNYA',
                    'FILE_SURAT_PINDAH'
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokSuratPindah . '/Arsip Surat Pindah';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipSuratPindah->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipSuratPindah->save();
                break;
            case 'Akta Perceraian':
                $infoArsipPerceraian = InfoArsipPerceraian::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokPerceraian = $infoArsipPerceraian->TAHUN_PEMBUATAN_DOK_PERCERAIAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_F201',
                    'FILE_FC_PP',
                    'FILE_KUTIPAN_PERKAWINAN',
                    'FILE_KTP',
                    'FILE_KK',
                    'FILE_SPTJM',
                    'FILE_LAINNYA',
                    'FILE_AKTA_PERCERAIAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokPerceraian . '/Arsip Perceraian';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipPerceraian->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipPerceraian->save();
                break;
            case 'Akta Pengesahan Anak':
                $infoArsipPengesahan = InfoArsipPengesahan::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokPengesahan = $infoArsipPengesahan->TAHUTAHUN_PEMBUATAN_DOK_PENGESAHAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_PENGESAHAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokPengesahan . '/Arsip Pengesahan';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipPengesahan->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipPengesahan->save();
                break;
            case 'Akta Kematian':
                $infoArsipKematian = infoArsipKematian::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokKematian = $infoArsipKematian->TAHUN_PEMBUATAN_DOK_KEMATIAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_F201',
                    'FILE_SK_KEMATIAN',
                    'FILE_KK',
                    'FILE_KTP',
                    'FILE_KTP_SUAMI_ISTRI',
                    'FILE_KUTIPAN_KEMATIAN',
                    'FILE_FC_PP',
                    'FILE_FC_DOK_PERJALANAN',
                    'FILE_DOK_PENDUKUNG',
                    'FILE_SPTJM',
                    'FILE_LAINNYA',
                    'FILE_AKTA_KEMATIAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        if (in_array($extension, $allowedExtensions)) {
                            if ($file->getSize() <= 25000000) {
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokKematian . '/Arsip Kematian';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipKematian->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipKematian->save();
                break;
            case 'Akta Kelahiran':
                $infoArsipKelahiran = InfoArsipKelahiran::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokKelahiran = $infoArsipKelahiran->TAHUN_PEMBUATAN_DOK_KELAHIRAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_KK',
                    'FILE_KTP_AYAH',
                    'FILE_KTP_IBU',
                    'FILE_F102',
                    'FILE_F201',
                    'FILE_BUKU_NIKAH',
                    'FILE_KUTIPAN_KELAHIRAN',
                    'FILE_SURAT_KELAHIRAN',
                    'FILE_SPTJM_PENERBITAN',
                    'FILE_PELAPORAN_KELAHIRAN',
                    'FILE_LAINNYA',
                    'FILE_AKTA_KELAHIRAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokKelahiran . '/Arsip Kelahiran';
                                $file->storeAs($folderPath, $fileName, 'public');
                                // Simpan nama file ke dalam database sesuai dengan field yang sesuai
                                $infoArsipKelahiran->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipKelahiran->save();
                break;
            case 'Akta Pengakuan Anak':
                $infoArsipPengakuan = InfoArsipPengakuan::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokPengakuan = $infoArsipPengakuan->TAHUN_PEMBUATAN_DOK_PENGAKUAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_PENGAKUAN',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokPengakuan . '/Arsip Pengakuan';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipPengakuan->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipPengakuan->save();
                break;
            case 'Akta Perkawinan':
                $infoArsipPerkawinan = InfoArsipPerkawinan::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokPerkawinan = $infoArsipPerkawinan->TAHUN_PEMBUATAN_DOK_PERKAWINAN;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_F201',
                    'FILE_FC_SK_KAWIN',
                    'FILE_FC_PASFOTO',
                    'FILE_KTP',
                    'FILE_KK',
                    'FILE_AKTA_KEMATIAN',
                    'FILE_AKTA_PERCERAIAN',
                    'FILE_SPTJM',
                    'FILE_LAINNYA',
                    'FILE_AKTA_PERKAWINAN'
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokPerkawinan . '/Arsip Perkawinan';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipPerkawinan->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipPerkawinan->save();
                break;
            case 'Kartu Keluarga':
                $infoArsipKk = InfoArsipKk::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokKk = $infoArsipKk->TAHUN_PEMBUATAN_DOK_KK;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_F101',
                    'FILE_NIKAH_CERAI',
                    'FILE_SK_PINDAH',
                    'FILE_SK_PINDAH_LUAR',
                    'FILE_SK_PENGGANTI',
                    'FILE_PUTUSAN_PRESIDEN',
                    'FILE_KK_LAMA',
                    'FILE_SK_PERISTIWA',
                    'FILE_SK_HILANG',
                    'FILE_KTP',
                    'FILE_LAINNYA',
                    'FILE_KK'
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokKk . '/Arsip Kk';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipKk ->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipKk ->save();
                break;
            case 'SKOT':
                $infoArsipSkot = InfoArsipSkot::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokSkot = $infoArsipSkot->TAHUN_PEMBUATAN_DOK_SKOT;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_SKOT'
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokSkot . '/Arsip Skot';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipSkot->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipSkot->save();
                break;
            case 'SKTT':
                $infoArsipSktt = InfoArsipSktt::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokSktt = $infoArsipSktt->TAHUN_PEMBUATAN_DOK_SKTT;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_LAINNYA',
                    'FILE_SKTT'
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokSktt . '/Arsip Sktt';
                                $file->storeAs($folderPath, $fileName, 'public');
                                $infoArsipSktt->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipSktt->save();
                break;
            case 'Kartu Tanda Penduduk':
                $infoArsipKtp = InfoArsipKtp::where('ID_ARSIP', $ID_ARSIP)->first();
                $tahunPembuatanDokKtp = $infoArsipKtp->TAHUN_PEMBUATAN_KTP;
                $fileFields = [
                    'FILE_LAMA',
                    'FILE_KK',
                    'FILE_KUTIPAN_KTP',
                    'FILE_SK_HILANG',
                    'FILE_AKTA_LAHIR',
                    'FILE_IJAZAH',
                    'FILE_SURAT_NIKAH_CERAI',
                    'FILE_SURAT_PINDAH',
                    'FILE_LAINNYA',
                    'FILE_KTP',
                ];

                // Loop melalui setiap field file untuk menyimpannya
                foreach ($fileFields as $field) {
                    if ($request->hasFile($field)) {
                        $allowedExtensions = ['pdf'];
                        $file = $request->file($field);
                        $extension = $file->getClientOriginalExtension();

                        // Periksa apakah ekstensi file diizinkan
                        if (in_array($extension, $allowedExtensions)) {
                            // Periksa ukuran file
                            if ($file->getSize() <= 25000000) { // Ukuran maksimum 25 MB
                                $fileName = $file->getClientOriginalName();
                                $folderPath = $tahunPembuatanDokKtp . '/Arsip Ktp';
                                $file->storeAs($folderPath, $fileName, 'public');
                                // Simpan path file ke dalam database sesuai dengan field yang sesuai
                                $infoArsipKtp->$field = $fileName;
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 25 MB.',
                                    'field' => $field
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ekstensi file tidak didukung. Hanya file PDF yang diizinkan.',
                                'field' => $field
                            ], 400);
                        }
                    }
                }
                $infoArsipKtp->save();
                break;
            // Tambahkan case lain sesuai dengan jenis dokumen yang ada
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis dokumen tidak didukung',
                    'jenis_dokumen' => $jenisDokumen,
                ], 400);
        }

        // Simpan dokumen sesuai dengan jenis dokumen
        Permission::where('ID_PERMISSION', $ID_PERMISSION)
          ->where('ID_ARSIP', $ID_ARSIP)
          ->update(['STATUS' => 'Disetujui']);
        // Mengembalikan respons berhasil
        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah'
        ], 200);
    }

    public function requestInput(Request $request)
    {
        $permissionRequest = new Permission();

        $validator = app('validator')->make($request->all(), [
            'ID_DOKUMEN' => 'nullable|integer',
            'NO_DOK_PENGANGKATAN' => 'nullable|string|unique:info_arsip_pengangkatan',
            'NO_DOK_SURAT_PINDAH' => 'nullable|string|unique:info_arsip_surat_pindah',
            'NO_DOK_PERCERAIAN' => 'nullable|string|unique:info_arsip_perceraian',
            'NO_DOK_PENGESAHAN' => 'nullable|string|unique:info_arsip_pengesahan',
            'NO_DOK_KEMATIAN' => 'nullable|string|unique:info_arsip_kematian',
            'NO_DOK_KELAHIRAN' => 'nullable|string|unique:info_arsip_kelahiran',
            'NO_DOK_PENGAKUAN' => 'nullable|string|unique:info_arsip_pengakuan',
            'NO_DOK_PERKAWINAN' => 'nullable|string|unique:info_arsip_perkawinan',
            'NO_DOK_KK' => 'nullable|string|unique:info_arsip_kk',
            'NO_DOK_SKOT' => 'nullable|string|unique:info_arsip_skot',
            'NO_DOK_SKTT' => 'nullable|string|unique:info_arsip_sktt',
            'NO_DOK_KTP' => 'nullable|string|unique:info_arsip_ktp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        // Membuat Arsip baru
        $arsip = new Arsip();
        $idDokumen = JenisDokumen::find($request->input('ID_DOKUMEN'));
        // Jika jenis dokumen tidak ditemukan
        if (!$idDokumen) {
            return response()->json(['error' => 'Jenis Dokumen tidak valid'], 400);
        }
        $arsip->ID_DOKUMEN = $idDokumen->ID_DOKUMEN; // Mengisi JENIS_DOKUMEN dari dropdown

        // Mengisi NO_DOK_ berdasarkan jenis dokumen yang dipilih
        switch ($idDokumen->NAMA_DOKUMEN) {
            case 'Akta Pengangkatan Anak':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipPengangkatan::where('NO_DOK_PENGANGKATAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_PENGANGKATAN = $noDokumen;
                $infoArsipPengangkatan = new InfoArsipPengangkatan();
                $infoArsipPengangkatan->NO_DOK_PENGANGKATAN = $arsip->NO_DOK_PENGANGKATAN;
                $infoArsipPengangkatan->NAMA_ANAK = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipPengangkatan, $permissionRequest) {
                    $arsip->save();
                    $infoArsipPengangkatan->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipPengangkatan->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Surat Pindah':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipSuratPindah::where('NO_DOK_SURAT_PINDAH', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_SURAT_PINDAH = $noDokumen;
                $infoArsipSuratPindah = new InfoArsipSuratPindah();
                $infoArsipSuratPindah->NO_DOK_SURAT_PINDAH = $arsip->NO_DOK_SURAT_PINDAH;
                $infoArsipSuratPindah->NAMA_KEPALA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipSuratPindah, $permissionRequest) {
                    $arsip->save();
                    $infoArsipSuratPindah->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipSuratPindah->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Perceraian':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipPerceraian::where('NO_DOK_PERCERAIAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_PERCERAIAN = $noDokumen;
                $infoArsipPerceraian = new InfoArsipPerceraian();
                $infoArsipPerceraian->NO_DOK_PERCERAIAN = $arsip->NO_DOK_PERCERAIAN;
                $infoArsipPerceraian->NAMA_PRIA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipPerceraian, $permissionRequest) {
                    $arsip->save();
                    $infoArsipPerceraian->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipPerceraian->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Pengesahan Anak':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipPengesahan::where('NO_DOK_PENGESAHAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_PENGESAHAN = $noDokumen;
                $infoArsipPengesahan = new InfoArsipPengesahan();
                $infoArsipPengesahan->NO_DOK_PENGESAHAN = $arsip->NO_DOK_PENGESAHAN;
                $infoArsipPengesahan->NAMA_ANAK = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipPengesahan, $permissionRequest) {
                    $arsip->save();
                    $infoArsipPengesahan->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipPengesahan->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Kematian':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipKematian::where('NO_DOK_KEMATIAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_KEMATIAN = $noDokumen;
                $infoArsipKematian = new InfoArsipKematian();
                $infoArsipKematian->NO_DOK_KEMATIAN = $arsip->NO_DOK_KEMATIAN;
                $infoArsipKematian->NAMA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipKematian, $permissionRequest) {
                    $arsip->save();
                    $infoArsipKematian->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipKematian->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Kelahiran':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipKelahiran::where('NO_DOK_KELAHIRAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_KELAHIRAN = $noDokumen;
                $infoArsipKelahiran = new InfoArsipKelahiran();
                $infoArsipKelahiran->NO_DOK_KELAHIRAN = $arsip->NO_DOK_KELAHIRAN;
                $infoArsipKelahiran->NAMA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipKelahiran, $permissionRequest) {
                    $arsip->save();
                    $infoArsipKelahiran->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipKelahiran->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Pengakuan Anak':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipPengakuan::where('NO_DOK_PENGAKUAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_PENGAKUAN = $noDokumen;
                $infoArsipPengakuan = new InfoArsipPengakuan();
                $infoArsipPengakuan->NO_DOK_PENGAKUAN = $arsip->NO_DOK_PENGAKUAN;
                $infoArsipPengakuan->NAMA_ANAK = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipPengakuan, $permissionRequest) {
                    $arsip->save();
                    $infoArsipPengakuan->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipPengakuan->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Akta Perkawinan':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipPerkawinan::where('NO_DOK_PERKAWINAN', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_PERKAWINAN = $noDokumen;
                $infoArsipPerkawinan = new InfoArsipPerkawinan();
                $infoArsipPerkawinan->NO_DOK_PERKAWINAN = $arsip->NO_DOK_PERKAWINAN;
                $infoArsipPerkawinan->NAMA_PRIA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipPerkawinan, $permissionRequest) {
                    $arsip->save();
                    $infoArsipPerkawinan->ID_ARSIP = $arsip->ID_ARSIP;
                    $infoArsipPerkawinan->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Kartu Keluarga':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipKk::where('NO_DOK_KK', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_KK = $noDokumen;
                $infoArsipKk = new InfoArsipKk();
                $infoArsipKk->NO_DOK_KK = $arsip->NO_DOK_KK;
                $infoArsipKk->NAMA_KEPALA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipKk, $permissionRequest) {
                    $arsip->save();
                    $$infoArsipKk->ID_ARSIP = $arsip->ID_ARSIP;
                    $$infoArsipKk->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'SKOT':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipSkot::where('NO_DOK_SKOT', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_SKOT = $noDokumen;
                $infoArsipSkot = new InfoArsipSkot();
                $infoArsipSkot->NO_DOK_SKOT = $arsip->NO_DOK_SKOT;
                $infoArsipSkot->NAMA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipSkot, $permissionRequest) {
                    $arsip->save();
                    $$infoArsipSkot->ID_ARSIP = $arsip->ID_ARSIP;
                    $$infoArsipSkot->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'SKTT':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipSktt::where('NO_DOK_SKTT', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_SKTT = $noDokumen;
                $infoArsipSktt = new InfoArsipSktt();
                $infoArsipSktt->NO_DOK_SKTT = $arsip->NO_DOK_SKTT;
                $infoArsipSktt->NAMA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipSktt, $permissionRequest) {
                    $arsip->save();
                    $$infoArsipSktt->ID_ARSIP = $arsip->ID_ARSIP;
                    $$infoArsipSktt->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            case 'Kartu Tanda Penduduk':
                $noDokumen = $request->input('NO_DOKUMEN');
                $namaDokumen = $request->input('NAMA');
                // Cek duplikasi NO_DOKUMEN di tabel info_arsip_pengangkatan
                $existingDocument = InfoArsipKtp::where('NO_DOK_KTP', $noDokumen)->first();
                if ($existingDocument) {
                    return response()->json([
                        'error' => 'Nomor dokumen sudah terdaftar, silakan gunakan nomor dokumen yang berbeda.'
                    ], 409); // Conflict HTTP status code
                }
                $arsip->NO_DOK_KTP = $noDokumen;
                $infoArsipKtp = new InfoArsipKtp();
                $infoArsipKtp->NO_DOK_KTP = $arsip->NO_DOK_KTP;
                $infoArsipKtp->NAMA = $namaDokumen;
                DB::transaction(function () use ($arsip, $infoArsipKtp, $permissionRequest) {
                    $arsip->save();
                    $$infoArsipKtp->ID_ARSIP = $arsip->ID_ARSIP;
                    $$infoArsipKtp->save();
                    $userRequestingId = Auth::id();
                    $permissionRequest->ID_OPERATOR = $userRequestingId;
                    $permissionRequest->STATUS = 'Request Input';
                    $permissionRequest->save();
                    $permissionRequest->ID_ARSIP = $arsip->ID_ARSIP;
                    $permissionRequest->save();
                });
                break;
            default:
                // Jika tidak ada kecocokan dengan NAMA_DOKUMEN yang diharapkan
                return response()->json(['error' => 'Jenis Dokumen tidak valid'], 400);
        }
        return response()->json([
            'success' => true,
            'message' => 'Arsip Telah Diinputkan',
            'permission' => $permissionRequest,
            'arsip' => $arsip,
        ], 200);
    }

    public function rejectedPermission(Request $request, $ID_PERMISSION)
    {
        $permissionRequest = Permission::find($ID_PERMISSION);

        // Jika permintaan tidak ditemukan, kembalikan respons dengan status 404
        if (!$permissionRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ijin tidak ditemukan',
            ], 404);
        }

        // Perbarui status permintaan menjadi "approved"
        $permissionRequest->update(['STATUS' => 'Ditolak']);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan ijin telah ditolak',
        ], 200);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Arsip;
use App\Models\Permission;
use App\Models\Operator;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        $permissions = Permission::all();

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
                    'arsips' => [], // Inisialisasi array untuk data arsip
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

                    $formattedPermission['arsips'][] = $formattedArsip;
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
        $userRequestingId = Auth::id();

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
        $userRequestingId = Auth::user(); // ID pengguna yang meminta akses

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

        // Perbarui status permintaan menjadi "DITOLAK"
        $permissionRequest->update(['STATUS' => 'Ditolak']);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan ijin telah ditolak',
        ], 200);
    }

}


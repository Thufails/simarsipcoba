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

        return response()->json([
            'success' => true,
            'message' => 'Data Permission Berhasil ditampilkan',
            'permissions' => $permissions
        ], 200);
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


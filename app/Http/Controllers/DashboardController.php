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

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function index ()
    {
        $historyPelayanan = HistoryPelayanan::all();

        $totalArsip = Arsip::count();

        $jumlahUserOnline = Operator::where('is_online', true)->count();

    }
    public function pencarian(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'jenis_dokumen' => 'nullable|exists:jenis_dokumen,ID_DOKUMEN',
            'no_dokumen' => 'nullable|string',
            'nama' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $jenisDokumen = JenisDokumen::when($request->has('jenis_dokumen'), function ($query) use ($request) {
            $query->where('ID_DOKUMEN', $request->jenis_dokumen);
        }) ->pluck('NAMA_DOKUMEN');

        $arsipquery = Arsip::query();

        if ($request->has('jenis_dokumen')) {
            $arsipquery->where('ID_DOKUMEN', $request->jenis_dokumen);
        }

        if ($request->has('no_dokumen')) {
            $arsipquery->where(function ($q) use ($request) {
                $q->where('NO_DOK_PENGANGKATAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_SURAT_PINDAH', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_PERCERAIAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_PENGESAHAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_KEMATIAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_KELAHIRAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_PENGAKUAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_PERKAWINAN', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_KK', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_SKOT', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_SKTT', 'LIKE', '%' . $request->no_dokumen . '%')
                    ->orWhere('NO_DOK_KTP', 'LIKE', '%' . $request->no_dokumen . '%');
            });
        }

        if ($request->has('nama')) {
            $arsipquery->where(function ($arsipquery) use ($request) {
                $models = [
                    'infoArsipPengangkatan' => 'NAMA_ANAK',
                    'infoArsipSuratPindah' => 'NAMA_KEPALA',
                    'infoArsipPerceraian' => ['NAMA_PRIA', 'NAMA_WANITA'],
                    'infoArsipPengesahan' => 'NAMA_ANAK',
                    'infoArsipKematian' => 'NAMA',
                    'infoArsipKelahiran' => 'NAMA',
                    'infoArsipPengakuan' => 'NAMA_ANAK',
                    'infoArsipPerkawinan' => ['NAMA_PRIA', 'NAMA_WANITA'],
                    'infoArsipKk' => 'NAMA_KEPALA',
                    'infoArsipSkot' => ['NAMA', 'NAMA_PANGGIL'],
                    'infoArsipSktt' => 'NAMA',
                    'infoArsipKtp' => 'NAMA',
                ];

                foreach ($models as $relation => $columnName) {
                    $arsipquery->orWhereHas($relation, function ($query) use ($request, $columnName) {
                        if (is_array($columnName)) {
                            $query->where($columnName[0], 'LIKE', '%' . $request->nama . '%')
                                ->orWhere($columnName[1], 'LIKE', '%' . $request->nama . '%');
                        } else {
                            $query->where($columnName, 'LIKE', '%' . $request->nama . '%');
                        }
                    });
                }
            });
        }

        $arsip = $arsipquery->select('JUMLAH_BERKAS', 'NO_BUKU', 'NO_RAK', 'NO_BARIS', 'NO_BOKS', 'LOK_SIMPAN', 'TANGGAL_PINDAI', 'KETERANGAN')->get();

        return response()->json([
            'jenis_dokumen'=>$jenisDokumen,
            'no_dokumen' => $request->no_dokumen,
            'nama' => $request->nama,
            'arsip' => $arsip,
        ]);
    }
    public function getAllArsip()
    {
        // Ambil semua arsip tanpa filter
        $arsips = Arsip::all();

        // Mengembalikan data dalam format JSON
        if ($arsips) {
            return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Data Arsip',
                'access_token' => $arsips
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan data Arsip',
                'data' => ''
            ], 400);
        }
    }
    public function manajemen ()
    {

    }
    public function rekapitulasi ()
    {

    }

    public function operator ()
    {

    }

    public function logout ()
    {

    }

}


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
        $arsips = Arsip::with('jenisDokumen')->get();

        if ($arsips->isNotEmpty()) {
            $formattedArsips = $arsips->map(function ($arsip) {
                return [
                    'ID_ARSIP' => $arsip->ID_ARSIP,
                    'ID_DOKUMEN' => $arsip->ID_DOKUMEN,
                    'NAMA_DOKUMEN' => $arsip->jenisDokumen->NAMA_DOKUMEN ?? null,
                    'NO_DOK_PENGANGKATAN' => $arsip->NO_DOK_PENGANGKATAN,
                    'NO_DOK_SURAT_PINDAH' => $arsip->NO_DOK_SURAT_PINDAH,
                    'NO_DOK_PERCERAIAN' => $arsip->NO_DOK_PERCERAIAN,
                    'NO_DOK_PENGESAHAN' => $arsip->NO_DOK_PENGESAHAN,
                    'ID_OPERATOR' => $arsip->ID_OPERATOR,
                    'ID_HISTORY' => $arsip->ID_HISTORY,
                    'NO_DOK_KEMATIAN' => $arsip->NO_DOK_KEMATIAN,
                    'NO_DOK_KELAHIRAN' => $arsip->NO_DOK_KELAHIRAN,
                    'NO_DOK_PENGAKUAN' => $arsip->NO_DOK_PENGAKUAN,
                    'NO_DOK_PERKAWINAN' => $arsip->NO_DOK_PERKAWINAN,
                    'NO_DOK_KK' => $arsip->NO_DOK_KK,
                    'NO_DOK_SKOT' => $arsip->NO_DOK_SKOT,
                    'NO_DOK_SKTT' => $arsip->NO_DOK_SKTT,
                    'NO_DOK_KTP' => $arsip->NO_DOK_KTP,
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

        return response()->json([
                'success' => true,
                'message' => 'Sukses Menampilkan Data Arsip',
                'arsips' => $formattedArsips
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data Arsip',
                'arsips' => []
            ], 404);
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


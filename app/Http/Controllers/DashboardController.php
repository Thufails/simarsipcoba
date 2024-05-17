<?php

namespace App\Http\Controllers;

use App\Models\Permission;
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

    public function rekapitulasi ()
    {
        try {
            // Menghitung total data dalam tabel arsip
            $total = Arsip::count();

            // Jika berhasil menghitung, kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menampilkan Data Arsip',
                'total_data' => $total
            ], 200);
        } catch (\Exception $e) {
            // Jika ada kesalahan, kembalikan response gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menampilkan Data Arsip',
                'data' => '',
            ], 500);
        }
    }

    public function requestToday()
    {

    }

    public function requestTotal()
    {
        try {
            // Menghitung total data dalam tabel arsip
            $total = Permission::count();

            // Jika berhasil menghitung, kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menampilkan Total Request',
                'total_request' => $total
            ], 200);
        } catch (\Exception $e) {
            // Jika ada kesalahan, kembalikan response gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menampilkan Data Request',
                'data' => '',
            ], 500);
        }
    }

    public function logout ()
    {

    }

}


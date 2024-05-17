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
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function index ()
    {
        try {
            // Menghitung total data dalam tabel arsip
            $totalArsip = Arsip::count();

            // Menghitung total data dalam tabel permission untuk hari ini
            $totalRequestToday = Permission::whereDate('created_at', Carbon::today())->count();

            // Menghitung total data dalam tabel permission
            $totalRequest = Permission::count();

            // Jika berhasil menghitung, kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menampilkan Dashboard',
                'total_data_arsip' => $totalArsip,
                'total_request_today' => $totalRequestToday,
                'total_all_request' => $totalRequest
            ], 200);
        } catch (\Exception $e) {
            // Jika ada kesalahan, kembalikan response gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menampilkan Data',
                'data' => '',
            ], 500);
        }
    }


    public function logout ()
    {

    }

}


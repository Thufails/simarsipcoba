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

    public function rekapitulasi ()
    {

    }

    public function logout ()
    {

    }

}


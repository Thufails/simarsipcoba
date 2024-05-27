<?php

namespace App\Http\Controllers;

use App\Models\HakAkses;
use App\Models\Operator;
use App\Models\Session;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OperatorController extends Controller
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

    public function showOperator()
    {
        // Mengambil data ID_SESSION, ID_OPERATOR dari tabel session dan EMAIL serta NAMA_OPERATOR dari tabel operator
        $session = Session::with('operator:ID_OPERATOR,EMAIL,NAMA_OPERATOR')
            ->select('ID_SESSION', 'ID_OPERATOR', 'STATUS')
            ->get();

        // Format data agar EMAIL dan NAMA_OPERATOR dapat ditampilkan dalam struktur JSON
        $sessionData = $session->map(function ($item) {
            return [
                'ID_SESSION' => $item->ID_SESSION,
                'ID_OPERATOR' => $item->ID_OPERATOR,
                'NAMA_OPERATOR' => $item->operator->NAMA_OPERATOR,
                'EMAIL' => $item->operator->EMAIL,
                'STATUS' => $item->STATUS,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data' => $sessionData,
        ], 200);
    }

}

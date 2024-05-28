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
        $operators = Operator::with('session')
            ->select('ID_OPERATOR', 'NAMA_OPERATOR', 'EMAIL','ID_AKSES')
            ->get();

        $operatorData = $operators->map(function ($operator) {
            $status = $operator->session->isEmpty() ? 'Nonaktif' : $operator->session->first()->STATUS;

            return [
                'ID_OPERATOR' => $operator->ID_OPERATOR,
                'NAMA_OPERATOR' => $operator->NAMA_OPERATOR,
                'EMAIL' => $operator->EMAIL,
                'ID_AKSES' => $operator->ID_AKSES,
                'STATUS' => $status,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Profile has been Showed',
            'data' => $operatorData,
        ], 200);
    }

}

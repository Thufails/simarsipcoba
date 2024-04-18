<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class DokumenController extends Controller
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

    public function uploadDokumen(Request $request)
    {
        $this->validate($request,[
            'jenis_dokumen' => 'required|max:255',
            'no_dokumen' => 'required|max:255',
            'nama' => 'required|max:255',
            'file_dokumen' => 'required|max:5000|mimes:pdf'
        ]);

        $dokumen = new Dokumen();

        // image upload
        if($request->hasFile('file_dokumen')) {

            $allowedfileExtension=['pdf'];
            $file = $request->file('file_dokumen');
            $extenstion = $file->getClientOriginalExtension();
            $check = in_array($extenstion, $allowedfileExtension);

            if($check){
                $nama = time() . $file->getClientOriginalName();
                $file->storeAs('pdf', $nama, 'public');
                $dokumen->file_dokumen = $nama;
            }
        }


        $dokumen->jenis_dokumen = $request->input('jenis_dokumen');
        $dokumen->no_dokumen = $request->input('no_dokumen');
        $dokumen->nama = $request->input('nama');
        // $dokumen->user_id = Auth::user()->id;
        $dokumen->save();
        if ($dokumen) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip berhasil ditambahkan',
                'data' => $dokumen
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Arsip gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    public function showAlldokumen()
    {
        $dokumen = Dokumen::all();

        if ($dokumen->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak Ditemukan',
                'data' => []
            ], 404);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen Ditemukan',
                'data' => $dokumen
            ], 200);
        }
    }

    public function showDokumenById($id)
    {
        $dokumen = Dokumen::find($id);

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen ditemukan',
            'data' => $dokumen
        ], 200);
    }

    public function updateDokumen(Request $request, $id)
    {
        $this->validate($request, [
            'jenis_dokumen' => 'required|max:255',
            'no_dokumen' => 'required|max:255',
            'nama' => 'required|max:255',
            'file_dokumen' => 'nullable|max:5000|mimes:pdf'
        ]);

        $dokumen = Dokumen::find($id);

        if (!$dokumen) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan',
                'data' => null
            ], 404);
        }

        if ($request->hasFile('file_dokumen')) {
            $allowedfileExtension = ['pdf'];
            $file = $request->file('file_dokumen');
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);

            if ($check) {
                $nama = time() . $file->getClientOriginalName();
                $file->storeAs('pdf', $nama, 'public');
                $dokumen->file_dokumen = $nama;
            }
        }

        $dokumen->jenis_dokumen = $request->input('jenis_dokumen');
        $dokumen->no_dokumen = $request->input('no_dokumen');
        $dokumen->nama = $request->input('nama');
        $dokumen->save();

        if ($dokumen) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupdate',
                'data' => $dokumen
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen gagal diupdate',
                'data' => null
            ], 400);
        }
    }
}

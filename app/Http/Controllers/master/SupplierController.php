<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;

class SupplierController extends Controller
{
    public function supplier_browse()
    {
        $supplier =  Supplier::all();

        $data['title'] = 'Master Supplier';
        return view('master/supplier', $data, compact('supplier'));
    }

    public function supplier_add(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:supplier',
            'nama' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required'
        ]);

        $supplier = new Supplier([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp
        ]);
        $supplier->save();        
        return redirect()->route('supplier')->with('success', 'Tambah data sukses!');
    } 

}

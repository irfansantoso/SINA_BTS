<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CurrencySinaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class CurrencySinaController extends Controller
{
    public function currencySina_browse()
    {   
        $currencySina =  CurrencySinaModel::all();

        $data['title'] = 'Master Currency';
        return view('master/currencySina', $data, compact('currencySina'));
    }

    public function currencySina_data(Request $request)
    {
        $data = CurrencySinaModel::orderBy('code_currency','asc')
                        ->get(['id_currency','code_currency','currency_description']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function currencySina_add(Request $request)
    {
        $request->validate([
            'code_currency' => 'required|unique:tb_currency',
            'currency_description' => 'required|unique:tb_currency'
        ]);

        $currencySina = new CurrencySinaModel([
            'code_currency' => $request->code_currency,
            'currency_description' => $request->currency_description,
            'created_by' => Auth::user()->name
        ]);

        $currencySina->save();        
        return redirect()->route('currencySina')->with('success', 'Tambah data sukses!');
    } 

    public function currencySina_edit($id)
    {
        // Find the user by ID
        $currencySina = CurrencySinaModel::findOrFail($id);

        // Return the user details as JSON
        return response()->json($currencySina);
    }

    public function currencySina_update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'code_currency' => 'required|max:100',
            'currency_description' => 'required|string|max:255'
        ]);

        // Find the CurrencySinaModel by ID
        $currencySina = CurrencySinaModel::findOrFail($id);

        // Update the user's details
        $currencySina->code_currency = $request->input('code_currency');
        $currencySina->currency_description = $request->input('currency_description');

        // Save the updated CurrencySinaModel 
        $currencySina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $currencySina,
        ]);
    }

    public function currencySina_delete($id)
    {
        $currencySina = CurrencySinaModel::findOrFail($id);
        $currencySina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

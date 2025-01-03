<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CompanySinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class CompanySinaController extends Controller
{
    public function companySina_browse()
    {   
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Company'.$showYearActive;
        return view('master/companySina', $data);
    }

    public function companySina_data(Request $request)
    {
        $data = CompanySinaModel::orderBy('id_company','asc')
                        ->get(['id_company','company_name']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function companySina_add(Request $request)
    {
        $request->validate([
            'company_name' => 'required|unique:tb_company'
        ]);

        $companySina = new CompanySinaModel([
            'company_name' => $request->company_name
        ]);

        $companySina->save();        
        // return redirect()->route('companySina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function companySina_edit($id_company)
    {
        // Find the user by ID
        $companySina = CompanySinaModel::findOrFail($id_company);

        // Return the user details as JSON
        return response()->json($companySina);
    }

    public function companySina_update(Request $request, $id_company)
    {
        // Validate the incoming request
        $request->validate([
            'company_name' => 'required'
        ]);

        // Find the CompanySinaModel by ID
        $companySina = CompanySinaModel::findOrFail($id_company);

        // Update the user's details
        $$companySina->company_name = $request->input('company_name');

        // Save the updated CompanySinaModel 
        $companySina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $companySina,
        ]);
    }

    public function companySina_delete($id_company)
    {
        $companySina = CompanySinaModel::findOrFail($id_company);
        $companySina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

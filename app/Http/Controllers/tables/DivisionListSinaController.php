<?php

namespace App\Http\Controllers\Tables;

use App\Http\Controllers\Controller;
use App\Models\DivisionListSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class DivisionListSinaController extends Controller
{
    public function divisionListSina_browse()
    {   
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Division List'.$showYearActive;
        return view('tables/divisionListSina', $data);
    }

    public function divisionListSina_data(Request $request)
    {
        $data = DivisionListSinaModel::orderBy('id_division','asc')
                        ->get(['id_division','code','division_name','category']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function divisionListSina_add(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:tb_division',
            'division_name' => 'required'
        ]);

        $divisionListSina = new DivisionListSinaModel([
            'code' => $request->code,
            'division_name' => $request->division_name,
            'category' => $request->category,
            'created_by' => Auth::user()->name
        ]);

        $divisionListSina->save();        
        // return redirect()->route('divisionListSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function divisionListSina_edit($id_division)
    {
        // Find the user by ID
        $divisionListSina = DivisionListSinaModel::findOrFail($id_division);

        // Return the user details as JSON
        return response()->json($divisionListSina);
    }

    public function divisionListSina_update(Request $request, $id_division)
    {
        // Validate the incoming request
        $request->validate([
            'code' => 'required',
            'division_name' => 'required'
        ]);

        // Find the DivisionListSinaModel by ID
        $divisionListSina = DivisionListSinaModel::findOrFail($id_division);

        // Update the user's details
        $divisionListSina->code = $request->input('code');
        $divisionListSina->division_name = $request->input('division_name');
        $divisionListSina->category = $request->input('category');

        // Save the updated DivisionListSinaModel 
        $divisionListSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $divisionListSina,
        ]);
    }

    public function divisionListSina_delete($id_division)
    {
        $divisionListSina = DivisionListSinaModel::findOrFail($id_division);
        $divisionListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

<?php

namespace App\Http\Controllers\Tables;

use App\Http\Controllers\Controller;
use App\Models\CostListSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class CostListSinaController extends Controller
{
    public function costListSina_browse()
    {   

        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Cost List'.$showYearActive;
        return view('tables/costListSina', $data);
    }

    public function costListSina_data(Request $request)
    {
        $data = CostListSinaModel::orderBy('id_cost','asc')
                        ->get(['id_cost','code_cost','cost_description','cost_category']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function costListSina_add(Request $request)
    {
        $request->validate([
            'code_cost' => 'required|unique:tb_cost',
            'cost_description' => 'required'
        ]);

        $costListSina = new CostListSinaModel([
            'code_cost' => $request->code_cost,
            'cost_description' => $request->cost_description,
            'cost_category' => $request->cost_category,
            'created_by' => Auth::user()->name
        ]);

        $costListSina->save();        
        // return redirect()->route('costListSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function costListSina_edit($id_cost)
    {
        // Find the user by ID
        $costListSina = CostListSinaModel::findOrFail($id_cost);

        // Return the user details as JSON
        return response()->json($costListSina);
    }

    public function costListSina_update(Request $request, $id_cost)
    {
        // Validate the incoming request
        $request->validate([
            'code_cost' => 'required',
            'cost_description' => 'required'
        ]);

        // Find the CostListSinaModel by ID
        $costListSina = CostListSinaModel::findOrFail($id_cost);

        // Update the user's details
        $costListSina->code_cost = $request->input('code_cost');
        $costListSina->cost_description = $request->input('cost_description');
        $costListSina->cost_category = $request->input('cost_category');

        // Save the updated CostListSinaModel 
        $costListSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $costListSina,
        ]);
    }

    public function costListSina_delete($id_cost)
    {
        $costListSina = CostListSinaModel::findOrFail($id_cost);
        $costListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

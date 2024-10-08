<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AccountTypeSinaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class AccountTypeSinaController extends Controller
{
    public function accountTypeSina_browse()
    {   
        $accountTypeSina =  AccountTypeSinaModel::all();

        $data['title'] = 'Master Account Type';
        return view('master/accountTypeSina', $data, compact('accountTypeSina'));
    }

    public function accountTypeSina_data(Request $request)
    {
        $data = AccountTypeSinaModel::orderBy('acc_no','asc')
                        ->get(['id','acc_no','acc_name','acc_type','acc_desc']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function accountTypeSina_add(Request $request)
    {
        $request->validate([
            'acc_no' => 'required|unique:tb_account_type',
            'acc_name' => 'required|unique:tb_account_type'
        ]);

        $accountTypeSina = new AccountTypeSinaModel([
            'acc_no' => $request->acc_no,
            'acc_name' => $request->acc_name,
            'acc_type' => $request->acc_type,
            'acc_desc' => $request->acc_desc,
            'created_by' => Auth::user()->name
        ]);

        $accountTypeSina->save();        
        return redirect()->route('accountTypeSina')->with('success', 'Tambah data sukses!');
    } 

    public function accountTypeSina_edit($id)
    {
        // Find the user by ID
        $accountTypeSina = AccountTypeSinaModel::findOrFail($id);

        // Return the user details as JSON
        return response()->json($accountTypeSina);
    }

    public function accountTypeSina_update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'acc_no' => 'required|max:100',
            'acc_name' => 'required|string|max:255'
        ]);

        // Find the AccountTypeSinaModel by ID
        $accountTypeSina = AccountTypeSinaModel::findOrFail($id);

        // Update the user's details
        $accountTypeSina->acc_no = $request->input('acc_no');
        $accountTypeSina->acc_name = $request->input('acc_name');
        $accountTypeSina->acc_type = $request->input('acc_type');
        $accountTypeSina->acc_desc = $request->input('acc_desc');

        // Save the updated AccountTypeSinaModel 
        $accountTypeSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $accountTypeSina,
        ]);
    }

    public function accountTypeSina_delete($id)
    {
        $accountTypeSina = AccountTypeSinaModel::findOrFail($id);
        $accountTypeSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

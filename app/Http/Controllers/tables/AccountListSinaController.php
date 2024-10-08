<?php

namespace App\Http\Controllers\Tables;

use App\Http\Controllers\Controller;
use App\Models\AccountListSinaModel;
use App\Models\AccountTypeSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class AccountListSinaController extends Controller
{
    public function accountListSina_browse()
    {   
        $accListSina= AccountListSinaModel::all();
        $accTypeSina= AccountTypeSinaModel::all();

        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Account List'.$showYearActive;
        return view('tables/accountListSina', $data, compact('accListSina','accTypeSina'));
    }

    public function accountListSina_data(Request $request)
    {
        // $data = DB::table(DB::raw("(SELECT tb_account_list.*, tb_account_type.acc_name AS typeName, parent_tb.account_name AS description FROM tb_account_list LEFT JOIN tb_account_type ON tb_account_list.type = tb_account_type.acc_no LEFT JOIN tb_account_list AS parent_tb ON tb_account_list.general_account = parent_tb.account_no) as tis"))
        //     ->orderBy('tis.id_acc_list', 'ASC');
        
        // Capture sorting parameters from the request
        $columnIndex = $request->input('order.0.column'); // Index of the column to be sorted
        $columnName = $request->input("columns.$columnIndex.name"); // Name of the column to be sorted
        $columnSortOrder = $request->input('order.0.dir'); // ASC or DESC

        // Base query
        $data = DB::table(DB::raw("(SELECT tb_account_list.*, tb_account_type.acc_name AS typeName, parent_tb.account_name AS description 
            FROM tb_account_list 
            LEFT JOIN tb_account_type ON tb_account_list.type = tb_account_type.acc_no 
            LEFT JOIN tb_account_list AS parent_tb ON tb_account_list.general_account = parent_tb.account_no) as tis"))
            ->select('tis.*');

        // Apply sorting based on the user's choice
        if ($columnName) {
            $data = $data->orderBy($columnName, $columnSortOrder);
        } else {
            $data = $data->orderBy('tis.id_acc_list', 'ASC'); // Default sorting
        }
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function accountListSina_add(Request $request)
    {
        $request->validate([
            'account_no' => 'required|unique:tb_account_list',
            'account_name' => 'required'
        ]);

        $accountListSina = new AccountListSinaModel([
            'account_no' => $request->account_no,
            'account_name' => $request->account_name,
            'type' => $request->type,
            'level' => $request->level,
            'category' => $request->category,
            'report' => $request->report,
            'general_account' => $request->general_account,
            'created_by' => Auth::user()->name
        ]);

        $accountListSina->save();        
        // return redirect()->route('accountListSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function accountListSina_edit($id_acc_list)
    {
        // Find the user by ID
        $accountListSina = AccountListSinaModel::findOrFail($id_acc_list);

        // Return the user details as JSON
        return response()->json($accountListSina);
    }

    public function accountListSina_update(Request $request, $id_acc_list)
    {
        // Validate the incoming request
        $request->validate([
            'account_no' => 'required',
            'account_name' => 'required'
        ]);

        // Find the AccountListSinaModel by ID
        $accountListSina = AccountListSinaModel::findOrFail($id_acc_list);

        // Update the user's details
        $accountListSina->account_no = $request->input('account_no');
        $accountListSina->account_name = $request->input('account_name');
        $accountListSina->type = $request->input('type');
        $accountListSina->level = $request->input('level');
        $accountListSina->category = $request->input('category');
        $accountListSina->report = $request->input('report');
        $accountListSina->general_account = $request->input('general_account');

        // Save the updated AccountListSinaModel 
        $accountListSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $accountListSina,
        ]);
    }

    public function accountListSina_delete($id_acc_list)
    {
        $accountListSina = AccountListSinaModel::findOrFail($id_acc_list);
        $accountListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

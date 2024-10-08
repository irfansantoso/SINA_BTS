<?php

namespace App\Http\Controllers\Tables;

use App\Http\Controllers\Controller;
use App\Models\JournalSourceCodeSinaModel;
use App\Models\JournalGroupSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class JournalSourceCodeSinaController extends Controller
{
    public function journalSourceCodeSina_browse()
    {   
        $journalGroupSina= JournalGroupSinaModel::all();

        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $syear = $getYearActive->year;

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $syear : '';

        $data['title'] = 'Journal Source Code'.$showYearActive;
        return view('tables/journalSourceCodeSina', $data, compact('journalGroupSina','syear'));
    }

    public function journalSourceCodeSina_data(Request $request)
    {
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $syear = $getYearActive->year;

        $data = DB::table(DB::raw("(SELECT * FROM tb_journal_sc) as tis"));
        $data->where('code_jgr', $request->code_jgr);
        $data->where('year', $syear);
        $data->orderBy('tis.id_jsc', 'ASC');
        
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function journalSourceCodeSina_cjgr($c_jgr)
    {
        // Find the user by ID
        $getDataJGR = JournalGroupSinaModel::select('code_jgr', 'description_jgr','deb_cre')
                                ->where('code_jgr', $c_jgr)
                                ->first(); // Fetch a single record        

        // Return the user details as JSON
        return response()->json($getDataJGR);
    }

    public function journalSourceCodeSina_add(Request $request)
    {
        // $request->validate([
        //     'account_no' => 'required|unique:tb_account_list',
        //     'account_name' => 'required'
        // ]);

        $journalSourceCodeSina = new JournalSourceCodeSinaModel([
            'code_jgr' => $request->code_jgr,
            'deb_cre' => $request->deb_cre,
            'year' => $request->year,
            'code_jrc' => $request->code_jrc,
            'journal_jrc_no' => $request->journal_jrc_no,
            'account_no' => $request->account_no,
            'account_name' => $request->account_name,
            'created_by' => Auth::user()->name
        ]);

        $journalSourceCodeSina->save();        
        // return redirect()->route('journalSourceCodeSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function journalSourceCodeSina_edit($id_jsc)
    {
        // Find the user by ID
        $journalSourceCodeSina = JournalSourceCodeSinaModel::findOrFail($id_jsc);

        // Return the user details as JSON
        return response()->json($journalSourceCodeSina);
    }

    public function journalSourceCodeSina_update(Request $request, $id_jsc)
    {
        // Validate the incoming request
        // $request->validate([
        //     'account_no' => 'required',
        //     'account_name' => 'required'
        // ]);

        // Find the AccountListSinaModel by ID
        $journalSourceCodeSina = JournalSourceCodeSinaModel::findOrFail($id_jsc);

        // Update the user's details
        $journalSourceCodeSina->code_jgr = $request->input('code_jgr');
        $journalSourceCodeSina->deb_cre = $request->input('deb_cre');
        $journalSourceCodeSina->year = $request->input('year');
        $journalSourceCodeSina->code_jrc = $request->input('code_jrc');
        $journalSourceCodeSina->journal_jrc_no = $request->input('journal_jrc_no');
        $journalSourceCodeSina->account_no = $request->input('account_no');
        $journalSourceCodeSina->account_name = $request->input('account_name');        

        // Save the updated journalSourceCodeSinaModel 
        $journalSourceCodeSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $journalSourceCodeSina,
        ]);
    }

    public function journalSourceCodeSina_delete($id_jsc)
    {
        $accountListSina = AccountListSinaModel::findOrFail($id_jsc);
        $accountListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

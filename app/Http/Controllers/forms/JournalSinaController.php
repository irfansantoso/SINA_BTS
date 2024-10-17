<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\JournalHeaderSinaModel;
use App\Models\JournalDetailSinaModel;
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

class JournalSinaController extends Controller
{
    public function journalSina_browse()
    {   
        $journalGroupSina= JournalGroupSinaModel::all();

        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $syear = $getYearActive->year;

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $syear : '';

        $data['title'] = 'Journal '.$showYearActive;
        return view('forms/journalSina', $data, compact('journalGroupSina','syear'));
    }

    public function journalSina_data(Request $request)
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

    public function journalSina_cjgr($c_jgr)
    {
        // Find the user by ID
        $getDataJGR = JournalGroupSinaModel::select('code_jgr', 'description_jgr','deb_cre')
                                ->where('code_jgr', $c_jgr)
                                ->first(); // Fetch a single record        

        // Return the user details as JSON
        return response()->json($getDataJGR);
    }

    public function journalSina_jsc($c_jgr)
    {
        // Find the user by ID
        $getDataJSC = JournalSourceCodeSinaModel::select('code_jrc')
                                ->where('code_jgr', $c_jgr)
                                ->get(); // Fetch a single record        

        // Return the user details as JSON
        return response()->json($getDataJSC);
    }

    public function journalSina_jsrNo($c_jgr,$c_jsr)
    {        
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $cp = $getYearActive->code_period;

        $getDataJsrNo = JournalHeaderSinaModel::select('journal_jrc_no')
                                ->where('code_jgr', $c_jgr)
                                ->where('code_jrc', $c_jsr)
                                ->where('code_period', $cp)
                                ->first(); // Fetch a single record  
        if(!empty($getDataJsrNo)){
            $nourut = substr($getDataJsrNo, 0, 4);
            $nourut++;            
            $newNo2 = sprintf("%04s", $nourut)."/";
            $jsrNo = $cp.$newNo2;
        }else{
            $jsrNo = $cp.'0001';
        }      
        
        return response()->json($jsrNo);
    }

    public function journalSina_add(Request $request)
    {
        // $request->validate([
        //     'account_no' => 'required|unique:tb_account_list',
        //     'account_name' => 'required'
        // ]);

        $journalHeaderSina = new JournalHeaderSinaModel([
            'code_jgr' => $request->code_jgr,
            'code_jsr' => $request->code_jsr,
            'journal_jrc_no' => $request->journal_jrc_no,
            'created_by' => Auth::user()->name
        ]);

        $journalHeaderSina->save();        
        // return redirect()->route('journalSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function journalSina_edit($id_jsc)
    {
        // Find the user by ID
        $journalSourceCodeSina = JournalSourceCodeSinaModel::findOrFail($id_jsc);

        // Return the user details as JSON
        return response()->json($journalSourceCodeSina);
    }

    public function journalSina_update(Request $request, $id_jsc)
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

    public function journalSina_delete($id_jsc)
    {
        $accountListSina = AccountListSinaModel::findOrFail($id_jsc);
        $accountListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

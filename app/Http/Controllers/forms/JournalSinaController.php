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

        if (Auth::check()) {
            $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                    ->where('user_acc_period', Auth::user()->username)
                                    ->first(); // Fetch a single record
        } else {
            return redirect()->route('login');
        }
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

    public function journalDetailSina_data(Request $request)
    {
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $syear = $getYearActive->year;

        $no_jjrc = $request->cpx.$request->journal_jrc_no;
        $JournalHeaderId = JournalHeaderSinaModel::select('id_journal_head')
                                ->where('journal_jrc_no', $no_jjrc)
                                ->where('code_jgr', $request->code_jgr)
                                ->where('code_jrc', $request->code_jrc)
                                ->first();
                                
        $jhi = $JournalHeaderId ? $JournalHeaderId->id_journal_head : 0;
        $data = DB::table(DB::raw("(SELECT * FROM tb_journal_detail) as tjd"))
                ->join('tb_account_list', 'tjd.account_no', '=', 'tb_account_list.account_no')
                ->where('tjd.journal_head_id', $jhi)
                ->orderBy('tjd.id_journal_detail', 'ASC')
                ->select('tjd.*', 'tb_account_list.account_name')
                ->get();

        // $data = DB::table(DB::raw("(SELECT * FROM tb_journal_detail) as tjd"))
        //         ->where('journal_head_id', $jhi)
        //         ->orderBy('id_journal_detail', 'ASC');
        
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

    public function journalSina_jsrNo($c_jgr,$c_jrc)
    {        
        $getYearActive = TempAccountingPeriodSinaModel::select('year','month','code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record        

        $cp = $getYearActive->code_period;
        $dt_periode = $getYearActive->month."/".$getYearActive->year;

        $getDataJsrNo = JournalHeaderSinaModel::select('journal_jrc_no')
                                ->where('code_jgr', $c_jgr)
                                ->where('code_jrc', $c_jrc)
                                ->where('code_period', $cp)
                                ->orderBy('id_journal_head', 'desc')
                                ->first(); // Fetch a single record
                                
        if($c_jrc!="null" AND $c_jrc!="XX"){
            if(!empty($getDataJsrNo)){
                $lastNumber  = substr($getDataJsrNo, -4);
                $nourut = (int)$lastNumber + 1;            
                $newNo = sprintf("%04d", $nourut);
                $jsrNo = $newNo;            
            }else{
                $jsrNo = '0001';
            }      
        }else{
            $jsrNo = '';
            $cp = '';
        }
        return response()->json(['cp' => $cp, 'jsrNo' => $jsrNo, 'dt_periode' => $dt_periode]);
    }

    public function journalSina_setFormByHeader($j_jrc_no,$c_jgr,$c_jrc)
    {
        $getYearActive = TempAccountingPeriodSinaModel::select('year','month','code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record        

        if (!$getYearActive) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data periode aktif tidak ditemukan.',
            ], 404);
        }
        
        $cp = $getYearActive->code_period;
        $dt_periode = $getYearActive->month."/".$getYearActive->year;
        // $jjrc_no = $cp.$j_jrc_no;

        $getDataSetFormByHeader = JournalHeaderSinaModel::select('journal_date','due_date','description')
                                ->where('code_jgr', $c_jgr)
                                ->where('code_jrc', $c_jrc)
                                ->where('journal_jrc_no', $j_jrc_no)
                                ->first(); // Fetch a single record  

        if (!$getDataSetFormByHeader) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data journal header tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $getDataSetFormByHeader,
            'periode' => $dt_periode, // Tambahkan informasi tambahan jika diperlukan
        ]);
            
    }

    public function journalSina_add(Request $request)
    {
        // $request->validate([
        //     'account_no' => 'required|unique:tb_account_list',
        //     'account_name' => 'required'
        // ]);
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record
        $cp = $getYearActive->code_period;

        $no_jjrc = $request->cpx.$request->journal_jrc_no;
        $journalHeaderSina = new JournalHeaderSinaModel([
            'code_jgr' => $request->code_jgr,
            'code_jrc' => $request->code_jrc,
            'journal_jrc_no' => $no_jjrc,                    
            'code_period' => $request->cpx,
            'journal_date' => $request->journal_date,
            'due_date' => $request->due_date,
            'description' => $request->description,
            'created_by' => Auth::user()->name
        ]);

        $journalHeaderSina->save();        
        // return redirect()->route('journalSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    }     

    public function journalSina_update(Request $request, $j_jrc_no,$c_jgr,$c_jrc)
    {

        $journalSinaUpdate = JournalHeaderSinaModel::where('journal_jrc_no', $j_jrc_no)
                                                    ->where('code_jgr', $c_jgr)
                                                    ->where('code_jrc', $c_jrc)
                                                    ->firstOrFail();

        // Update the user's details
        $journalSinaUpdate->journal_date = $request->input('journal_date');
        $journalSinaUpdate->due_date = $request->input('due_date');
        $journalSinaUpdate->description = $request->input('description');      

        // Save the updated journalSinaUpdateModel 
        $journalSinaUpdate->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'data' => $journalSinaUpdate,
        ]);
    }

    public function journalSina_delete($id_jsc)
    {
        $accountListSina = AccountListSinaModel::findOrFail($id_jsc);
        $accountListSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

    public function journalDetailSina_add(Request $request)
    {
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first();

        $cp = $getYearActive->code_period;
        $no_jjrc = $request->cpx.$request->journal_jrc_no;

        $JournalHeaderId = JournalHeaderSinaModel::select('id_journal_head')
                                ->where('journal_jrc_no', $no_jjrc)
                                ->where('code_jgr', $request->code_jgr)
                                ->where('code_jrc', $request->code_jrc)
                                ->first();
        $jhi = $JournalHeaderId->id_journal_head;
        
        $journalDetailSina = new JournalDetailSinaModel([
            'journal_head_id' => $jhi,                    
            'code_period' => $request->cpx,
            'account_no' => $request->account_no,
            'code_cost' => $request->code_cost,
            'code_div' => $request->code_div,
            'invoice_no' => $request->invoice_no,
            'code_currency' => $request->code_currency,
            'debit' => $request->debit,
            'kredit' => $request->kredit,
            'kurs' => $request->kurs,
            'jumlah_total' => $request->jumlah_total,
            'description_detail' => $request->description_detail,
            'created_by' => Auth::user()->name
        ]);

        $journalDetailSina->save();        
        // return redirect()->route('journalSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    }

    public function journalDetailSina_edit($id_jd)
    {
        // Find the user by ID
        // $journalDetailSina = JournalDetailSinaModel::findOrFail($id_jd);
        $journalDetailSina = JournalDetailSinaModel::join('tb_account_list', 'tb_journal_detail.account_no', '=', 'tb_account_list.account_no')
            ->where('tb_journal_detail.id_journal_detail', $id_jd)
            ->select('tb_journal_detail.*', 'tb_account_list.account_name as account_name')
            ->first();

        // Return the user details as JSON
        return response()->json($journalDetailSina);
    }

    public function journalDetailSina_update(Request $request, $id_jd)
    {

        $journalDetailSinaUpdate = JournalDetailSinaModel::where('id_journal_detail', $id_jd)
                                                        ->firstOrFail();

        $journalDetailSinaUpdate->account_no = $request->input('account_no');
        $journalDetailSinaUpdate->code_cost = $request->input('code_cost');
        $journalDetailSinaUpdate->code_div = $request->input('code_div');
        $journalDetailSinaUpdate->invoice_no = $request->input('invoice_no');
        $journalDetailSinaUpdate->code_currency = $request->input('code_currency');
        $journalDetailSinaUpdate->debit = $request->input('debit');
        $journalDetailSinaUpdate->kredit = $request->input('kredit');
        $journalDetailSinaUpdate->kurs = $request->input('kurs');
        $journalDetailSinaUpdate->jumlah_total = $request->input('jumlah_total');
        $journalDetailSinaUpdate->description_detail = $request->input('description_detail');

        // Save the updated journalDetailSinaUpdateModel
        $journalDetailSinaUpdate->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'data' => $journalDetailSinaUpdate,
        ]);
    }

    public function journalDetailSina_delete($id_jd)
    {
        $journalDetailSina = JournalDetailSinaModel::findOrFail($id_jd);
        $journalDetailSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

<?php

namespace App\Http\Controllers\Gl;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriodSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class AccountingPeriodSinaController extends Controller
{
    public function accountingPeriodSina_browse()
    {   
        // dd(Auth::user());
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Accounting Period'.$showYearActive;
        return view('general_ledger/accountingPeriodSina', $data);
    }

    public function accountingPeriodSina_data(Request $request)
    {
        // $data = AccountingPeriodSinaModel::orderBy('id_period','asc')
        //                 ->get(['id_period','year','month','start_date','end_date','status_period']);

        $data = AccountingPeriodSinaModel::select(
            'accounting_period.id_period',
            'accounting_period.year',
            'accounting_period.month',
            'accounting_period.start_date',
            'accounting_period.end_date',
            'accounting_period.code_period',
            'temp_acc_period.user_acc_period'
        )
        ->leftJoin('temp_acc_period', 'accounting_period.code_period', '=', 'temp_acc_period.code_period')
        ->where(function($query) {
            $query->where('temp_acc_period.user_acc_period', Auth::user()->username)
                  ->orWhereNull('temp_acc_period.user_acc_period'); // Include records without a match
        })
        ->orderBy('accounting_period.id_period', 'asc')
        ->get();

        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function accountingPeriodSina_add(Request $request)
    {
        $request->validate([
            'year' => 'required',
            'month' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);
        $code_period = substr($request->year, 2) . str_pad($request->month, 2, "0", STR_PAD_LEFT);

        $accountingPeriodSina = new AccountingPeriodSinaModel([
            'year' => $request->year,
            'month' => $request->month,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'code_period' => $code_period
        ]);

        $accountingPeriodSina->save();        
        // return redirect()->route('accountingPeriodSina')->with('success', 'Tambah data sukses!');
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function accountingPeriodSina_edit($id_period)
    {
        // Find the user by ID
        $accountingPeriodSina = AccountingPeriodSinaModel::findOrFail($id_period);

        // Return the user details as JSON
        return response()->json($accountingPeriodSina);
    }

    public function accountingPeriodSina_update(Request $request, $id_period)
    {
        // Validate the incoming request
        $request->validate([
            'year' => $request->year,
            'month' => $request->month,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        // Find the AccountingPeriodSinaModel by ID
        $accountingPeriodSina = AccountingPeriodSinaModel::findOrFail($id_period);

        // Update the user's details
        $accountingPeriodSina->year = $request->input('year');
        $accountingPeriodSina->month = $request->input('month');
        $accountingPeriodSina->start_date = $request->input('start_date');
        $accountingPeriodSina->end_date = $request->input('end_date');

        // Save the updated AccountingPeriodSinaModel 
        $accountingPeriodSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $accountingPeriodSina,
        ]);
    }

    public function accountingPeriodSina_updateStatus(Request $request, $id_period)
    {
        // Auth::user()->username;
        $deleted = DB::table('temp_acc_period')
                 ->where('user_acc_period', Auth::user()->username)
                 ->delete();
        // Find the AccountingPeriodSinaModel by ID
        $accountingPeriodSina_updateStatus = AccountingPeriodSinaModel::findOrFail($id_period);

        // Update the user's details
        // $accountingPeriodSina_updateStatus->status_period = 1;

        // Save the updated AccountingPeriodSinaModel 
        // $accountingPeriodSina_updateStatus->save();

        // Insert the relevant data into temp_acc_period
        DB::table('temp_acc_period')->insert([
            'year' => $accountingPeriodSina_updateStatus->year,
            'month' => $accountingPeriodSina_updateStatus->month, 
            'code_period' => $accountingPeriodSina_updateStatus->code_period, 
            'user_acc_period' => Auth::user()->username,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Update Status Data successfully',
            'user' => $accountingPeriodSina_updateStatus,
        ]);
    }

    public function accountingPeriodSina_delete($id_period)
    {
        $accountingPeriodSina = AccountingPeriodSinaModel::findOrFail($id_period);
        $accountingPeriodSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

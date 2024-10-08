<?php

namespace App\Http\Controllers\Tables;

use App\Http\Controllers\Controller;
use App\Models\JournalGroupSinaModel;
use App\Models\TempAccountingPeriodSinaModel;
use Illuminate\Http\Request;
use Illuminate\Http\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Session;

class JournalGroupSinaController extends Controller
{
    public function journalGroupSina_browse()
    {   
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        $data['title'] = 'Journal Group'.$showYearActive;
        return view('tables/journalGroupSina', $data);
    }

    public function journalGroupSina_data(Request $request)
    {
        $data = JournalGroupSinaModel::orderBy('id_jgr','asc')
                        ->get(['id_jgr','code_jgr','description_jgr','deb_cre']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function journalGroupSina_add(Request $request)
    {
        $request->validate([
            'code_jgr' => 'required|unique:tb_journal_group',
            'description_jgr' => 'required'
        ]);

        $journalGroupSina = new JournalGroupSinaModel([
            'code_jgr' => $request->code_jgr,
            'description_jgr' => $request->description_jgr,
            'deb_cre' => $request->deb_cre,
            'created_by' => Auth::user()->name
        ]);

        $journalGroupSina->save();        
        return response()->json(['success' => 'Tambah data sukses!']);
    } 

    public function journalGroupSina_edit($id_jgr)
    {
        // Find the user by ID
        $journalGroupSina = JournalGroupSinaModel::findOrFail($id_jgr);

        // Return the user details as JSON
        return response()->json($journalGroupSina);
    }

    public function journalGroupSina_update(Request $request, $id_jgr)
    {
        // Validate the incoming request
        $request->validate([
            'code_jgr' => 'required',
            'description_jgr' => 'required'
        ]);

        // Find the JournalGroupSinaModel by ID
        $journalGroupSina = JournalGroupSinaModel::findOrFail($id_jgr);

        // Update the user's details
        $journalGroupSina->code_jgr = $request->input('code_jgr');
        $journalGroupSina->description_jgr = $request->input('description_jgr');
        $journalGroupSina->deb_cre = $request->input('deb_cre');

        // Save the updated JournalGroupSinaModel 
        $journalGroupSina->save();

        // Return a success response
        return response()->json([
            'message' => 'Ubah Data successfully',
            'user' => $journalGroupSina,
        ]);
    }

    public function journalGroupSina_delete($id_jgr)
    {
        $journalGroupSina = JournalGroupSinaModel::findOrFail($id_jgr);
        $journalGroupSina->delete();
        
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }

}

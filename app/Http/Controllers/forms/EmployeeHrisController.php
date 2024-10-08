<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\EmployeeHris;
use App\Models\HistoryRenewalEmployeeHris;
use App\Models\DepartmentHris;
use App\Models\SiteHris;
use App\Models\PositionHris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Session;

class EmployeeHrisController extends Controller
{
    public function employee_data(Request $request)
    {
        $cek_active = $request->get('cek_active'); // Get the checkbox value from the request
        if ($cek_active === 'yes') {
            // $query->where('tb_employee.information_status', '<>', 'active');
            $where = "tb_employee.information_status <> 'active'";
        } else {
            // $query->where('tb_employee.information_status', 'active');
            $where = "tb_employee.information_status = 'active'";
        }

        $query = DB::table(DB::raw("(SELECT tb_employee.*, tb_site.site_name AS siteName, tb_department.dept_name AS deptName, 
    tb_position.position_name AS positionName FROM tb_employee LEFT JOIN tb_site ON tb_employee.site_id = tb_site.id_site LEFT JOIN tb_department ON tb_employee.dept_id = tb_department.id_dept
          LEFT JOIN tb_position ON tb_employee.position_id = tb_position.id_position WHERE ".$where.") as tis"));
                
        return Datatables::of($query)
                    ->addIndexColumn()
                    ->make(true);
    }

    public function employee()
    {
        $data['title'] = 'Employee List';
        return view('forms/employeeHris', $data);
    }

    public function employee_add()
    {
        $deptHris= DepartmentHris::all();
        $positionHris= PositionHris::all();
        $siteHris= SiteHris::all();
        $data['title'] = 'Add New Employee';
        return view('forms/employeeHris_add', $data, compact('deptHris','siteHris','positionHris'));
    }

    public function employee_save(Request $request)
    {

        $request->validate([
            'employee_number' => 'required|unique:tb_employee',
            'site_id' => 'required',
            'dept_id' => 'required',
            'employee_name' => 'required',
            'nik' => 'required|unique:tb_employee',
            'start_contract' => 'required',
            'end_contract' => 'required'
        ]);

        $employeeHris = new EmployeeHris([
            'employee_number' => $request->employee_number,
            'dept_id' => $request->dept_id,
            'site_id' => $request->site_id,
            'employee_name' => $request->employee_name,
            'nik' => $request->nik,
            'bpjs_tk' => $request->bpjs_tk,
            'bpjs_kes' => $request->bpjs_kes,
            'date_in' => $request->date_in,
            'place_birth' => $request->place_birth,
            'date_birth' => $request->date_birth,
            'position_id' => $request->position_id,
            'status_marital' => $request->status_marital,
            'gender' => $request->gender,
            'fee_status' => $request->fee_status,
            'religion' => $request->religion,
            'education' => $request->education,
            'recipient_address' => $request->recipient_address,
            'start_contract' => $request->start_contract,
            'end_contract' => $request->end_contract,
            'duration_contract' => $request->duration_contract,
            'information_status'=> "active",
            'created_by' => Auth::user()->name
        ]);
        $employeeHris->save();

        $histRenewalEmployeeHris = new HistoryRenewalEmployeeHris([
            'nik' => $request->nik,
            'start_contract_renew' => $request->start_contract,
            'end_contract_renew' => $request->end_contract,
            'duration_contract_renew' => $request->duration_contract,
            'created_by' => Auth::user()->name
        ]);
        $histRenewalEmployeeHris->save();

        return redirect()->route('employeeHris_add')->with('success', 'Tambah data sukses!');
    }

    public function employee_detail($nik)
    {
        // $employee = EmployeeHris::findOrFail($id);
        $employee = EmployeeHris::where('nik', $nik)->firstOrFail();
        $deptHris = DepartmentHris::where('id_dept', $employee->dept_id)->firstOrFail();
        $positionHris = PositionHris::where('id_position', $employee->position_id)->firstOrFail();
        $siteHris = SiteHris::where('id_site', $employee->site_id)->firstOrFail();
        $histRenewEmp = HistoryRenewalEmployeeHris::where('nik', $employee->nik)
                                                    ->orderBy('id_hist_renew', 'asc')
                                                    ->get();
        // $histRenewEmp = HistoryRenewalEmployeeHris::where('nik', $employee->nik)
        //                                             ->orderBy('id_hist_renew', 'asc')
        //                                             ->firstOrFail();
        $data['title'] = 'Detail Employee';
        return view('forms.employeeHris_detail', $data, compact('employee', 'siteHris', 'deptHris', 'positionHris' ,'histRenewEmp'));
    }

    public function employee_edit($id)
    {
        $employee = EmployeeHris::findOrFail($id);
        $deptHris = DepartmentHris::all();
        $positionHris = PositionHris::all();
        $siteHris= SiteHris::all();
        $data['title'] = 'Edit Employee';
        return view('forms.employeeHris_add', $data, compact('employee', 'siteHris', 'deptHris', 'positionHris'));
    }

    public function employee_update(Request $request, $id)
    {
        $request->validate([
            'employee_number' => 'required',
            'site_id' => 'required',
            'dept_id' => 'required',
            'employee_name' => 'required',
            'nik' => 'required',
            'bpjs_tk' => 'required',
            'bpjs_kes' => 'required',
            'date_in' => 'required',
            'place_birth' => 'required',
            'date_birth' => 'required',
            'position_id' => 'required',
            'status_marital' => 'required',
            'gender' => 'required',
            'fee_status' => 'required',
            'religion' => 'required',
            'education' => 'required',
            'recipient_address' => 'required',
            'start_contract' => 'required',
            'end_contract' => 'required'
        ]);        

        try {
            $employeeHris = EmployeeHris::findOrFail($id);
            $employeeHris->update($request->all());
            // Update the updated_at field
            $employeeHris->updated_by = Auth::user()->name;
            $employeeHris->updated_at = Carbon::now(); // Or use now() if it's already imported
            $employeeHris->save();

            $histRenewalEmployeeHris = HistoryRenewalEmployeeHris::where('nik', $request->nik)
                                        ->orderBy('id_hist_renew', 'desc')
                                        ->first();

            if ($histRenewalEmployeeHris) {
                $histRenewalEmployeeHris->update([
                    'start_contract_renew' => $request->start_contract,
                    'end_contract_renew' => $request->end_contract,
                    'duration_contract_renew' => $request->duration_contract,
                    'created_by' => Auth::user()->name,
                    'updated_at' => Carbon::now()
                ]);
            } else {
                return response()->json(['message' => 'No matching record found'], 404);
            }

            return redirect()->route('employeeHris')->with('success', 'Update data sukses!');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function employee_emp_renewal(Request $request)
    {
        $request->validate([
            'start_contract' => 'required',
            'end_contract' => 'required'
        ]);        

        try {
            $employeeHris = EmployeeHris::findOrFail($request->id_employee);
            // $employeeHris->update($request->all());
            $employeeHris->update([
                                    'start_contract' => $request->start_contract,
                                    'end_contract' => $request->end_contract,
                                    'duration_contract' => $request->duration_contract,
                                    'information_status'=> "active",
                                    'updated_by' => Auth::user()->name,
                                    'updated_at' => Carbon::now()
                                ]);

            $histRenewalEmployeeHris = new HistoryRenewalEmployeeHris([
                'nik' => $request->nik,
                'start_contract_renew' => $request->start_contract,
                'end_contract_renew' => $request->end_contract,
                'duration_contract_renew' => $request->duration_contract,
                'created_by' => Auth::user()->name
            ]);
            $histRenewalEmployeeHris->save();        

            return redirect()->route('employeeHris')->with('success', 'Update data sukses!');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function employee_emp_del(Request $request)
    {    

        $employeeHris = EmployeeHris::findOrFail($request->id_employee);        
        $employeeHris->update([
                                'information_status' => $request->information_status,
                                'updated_by' => Auth::user()->name,
                                'updated_at' => Carbon::now()
                            ]);

        return redirect()->route('employeeHris')->with('success', 'Update data sukses!');
    }

}

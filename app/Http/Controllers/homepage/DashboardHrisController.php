<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use App\Models\EmployeeHris;
use App\Models\HistoryRenewalEmployeeHris;
use App\Models\DepartmentHris;
use App\Models\PositionHris;
use App\Models\TempAccountingPeriodSinaModel;
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

class DashboardHrisController extends Controller
{
    public function employee_data(Request $request)
    {

        $data = DB::table(DB::raw("(SELECT tb_employee.*, tb_site.site_name AS siteName, tb_department.dept_name AS deptName, 
    tb_position.position_name AS positionName FROM tb_employee LEFT JOIN tb_site ON tb_employee.site_id = tb_site.id_site LEFT JOIN tb_department ON tb_employee.dept_id = tb_department.id_dept
          LEFT JOIN tb_position ON tb_employee.position_id = tb_position.id_position WHERE tb_employee.information_status = 'active' AND (
        tb_employee.end_contract BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        OR tb_employee.end_contract < CURDATE()
    )) as tis"));
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function dashboard()
    {
        $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                                ->where('user_acc_period', Auth::user()->username)
                                ->first(); // Fetch a single record

        // Check if a record was found
        $showYearActive = $getYearActive ? ' - ' . $getYearActive->year : '';

        if (Auth::check()) {
            $data['title'] = 'Dashboard'.$showYearActive;
            return view('homepages/dashboard', $data);
        }else{
            return redirect('login');
        }
    }    

}

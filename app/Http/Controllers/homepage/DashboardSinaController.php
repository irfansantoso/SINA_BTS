<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
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

class DashboardSinaController extends Controller
{    
    public function dashboard()
    {
        if (Auth::check()) {
            $username = Auth::user()->username;
            $getYearActive = TempAccountingPeriodSinaModel::select('year', 'code_period')
                            ->where('user_acc_period', $username)
                            ->first();
        } else {
            return redirect()->route('login')->with('error', 'Please log in to access this site.');
        }

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

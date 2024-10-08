<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DepartmentHris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;

class DepartmentHrisController extends Controller
{
    public function department_browse()
    {
        $departmentHris =  DepartmentHris::all();

        $data['title'] = 'Master Department';
        return view('master/departmentHris', $data, compact('departmentHris'));
    }

    public function department_add(Request $request)
    {
        $request->validate([
            'dept_name' => 'required'
        ]);

        $departmentHris = new DepartmentHris([
            'dept_name' => $request->dept_name
        ]);
        $departmentHris->save();        
        return redirect()->route('departmentHris')->with('success', 'Tambah data sukses!');
    } 

}

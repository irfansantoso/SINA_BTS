<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SiteSina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;

class SiteSinaController extends Controller
{
    public function site_browse()
    {
        $siteSina =  SiteSina::all();

        $data['title'] = 'Master Site';
        return view('master/siteSina', $data, compact('siteSina'));
    }

    public function site_add(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'site_location' => 'required'
        ]);

        $siteSina = new SiteSina([
            'site_name' => $request->site_name,
            'site_location' => $request->site_location
        ]);
        $siteSina->save();        
        return redirect()->route('siteSina')->with('success', 'Tambah data sukses!');
    } 

}

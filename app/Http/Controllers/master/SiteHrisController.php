<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SiteHris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;

class SiteHrisController extends Controller
{
    public function site_browse()
    {
        $siteHris =  SiteHris::all();

        $data['title'] = 'Master Site';
        return view('master/siteHris', $data, compact('siteHris'));
    }

    public function site_add(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'site_location' => 'required'
        ]);

        $siteHris = new SiteHris([
            'site_name' => $request->site_name,
            'site_location' => $request->site_location
        ]);
        $siteHris->save();        
        return redirect()->route('siteHris')->with('success', 'Tambah data sukses!');
    } 

}

<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\EmailReceiverHris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Session;

class EmailReceiverHrisController extends Controller
{
    public function emailReceiver_browse()
    {
        $emailReceiver =  EmailReceiverHris::all();

        $data['title'] = 'Master Email Receiver';
        return view('master/emailReceiverHris', $data, compact('emailReceiver'));
    }

    public function emailReceiver_add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);

        $emailReceiver = new EmailReceiverHris([
            'name' => $request->name,
            'email' => $request->email
        ]);
        $emailReceiver->save();        
        return redirect()->route('emailReceiverHris')->with('success', 'Tambah data sukses!');
    } 

    public function emailReceiver_edit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required'
        ]);        

        try {
            $emailReceiver = EmailReceiverHris::findOrFail($request->id_receiver);          
            $emailReceiver->update([
                                    'name' => $request->name,
                                    'email' => $request->email,
                                    'updated_at' => Carbon::now()
                                ]);            

            return redirect()->route('emailReceiverHris')->with('success', 'Update data sukses!');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}

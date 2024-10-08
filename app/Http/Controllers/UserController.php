<?php

namespace App\Http\Controllers;

use App\Models\User;
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


class UserController extends Controller
{

    public function login()
    {
        if (Auth::check()) {
            return redirect('dashboard');
        }else{
            return view('login');
        }
    }

    public function login_action(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect('dashboard');
        }

        return back()->withErrors([
            'password' => 'Wrong username or password',
        ]);
    }    

    public function register()
    {
        $data['title'] = '';
        return view('user/register', $data);
    }

    public function register_action(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:tb_user',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
        ]);

        $user = new User([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);
        $user->save();

        return redirect()->route('login')->with('success', 'Registration success. Please login!');
    }

    public function users_data(Request $request)
    {
        $data = User::orderBy('name','asc')
                        ->get(['user_id','name','username','level']);
        return Datatables::of($data)
                        ->addIndexColumn()
                        ->make(true);
    }

    public function users()
    {
        //$user =  User::all();

        $data['title'] = 'Register User';
        //return view('master/users', $data, compact('user'));
        return view('master/users', $data);
    }

    public function users_add(Request $request)
    {
        // echo $request;
        // exit();
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:tb_user',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
            'level' => 'required',
        ]);

        $user = new User([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'level' => $request->level,
        ]);
        $user->save();

        return redirect()->route('users')->with('success', 'Tambah data sukses!');
    }

    public function users_reset($id)
    {
        $user = User::findOrFail($id);

        $user->update([
                        'password' => Hash::make('123456'),
                        'updated_at' => Carbon::now()
                    ]);
        return redirect()->route('users')->with('success', 'Reset data sukses! Default Password is 123456');
    }

    public function edit($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Return the user details as JSON
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:tb_user',
            'level' => 'required|string|in:administrator,user',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user's details
        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->level = $request->input('level');

        // Update the password only if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Save the updated user information
        $user->save();

        // Return a success response
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function profile()
    {
        $user =  User::all();

        $data['title'] = 'Profile';
        return view('master/profile', $data, compact('user'));
    }

    public function profile_edit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'photo_img.*' => 'mimes:jpg,jpeg,png|max:2000',
        ]);

        if ($request->hasfile('photo_img')) {

            $image_path = public_path() . "/photos/".Auth::user()->photo_img; 
            if(File::exists($image_path)) {
                File::delete($image_path);
            }

            $photo_img = round(microtime(true) * 1000).'-'.str_replace(' ','-',$request->file('photo_img')->getClientOriginalName());
            $request->file('photo_img')->move(public_path('photos'), $photo_img);
            if($request->password != '')
            {
                User::where('username', Auth::user()->username)
                      ->update(['name' => $request->name,
                                'password' => Hash::make($request->password),
                                'level' => $request->level,
                                'photo_img' => $photo_img,
                                'updated_at' => date('Y-m-d H:i:s'),
                                    ]);      
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/');
            }else{
                User::where('username', Auth::user()->username)
                  ->update(['name' => $request->name,
                            'level' => $request->level,                            
                            'photo_img' => $photo_img,
                            'updated_at' => date('Y-m-d H:i:s'),
                                ]);      
            return redirect()->route('profile')->with('success', 'Ubah data sukses!');    
            }
        }else{
            if($request->password != '')
            {
                User::where('username', Auth::user()->username)
                      ->update(['name' => $request->name,
                                'password' => Hash::make($request->password),
                                'level' => $request->level, 
                                'updated_at' => date('Y-m-d H:i:s'),
                                    ]);      
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/');
            }else{
                User::where('username', Auth::user()->username)
                      ->update(['name' => $request->name,
                                'level' => $request->level, 
                                'updated_at' => date('Y-m-d H:i:s'),
                                    ]);      
                return redirect()->route('profile')->with('success', 'Ubah data sukses!');
            }
        }
    }

    public function password()
    {
        $data['title'] = 'Change Password';
        return view('user/password', $data);
    }

    public function password_action(Request $request)
    {
        $request->validate([
            'old_password' => 'required|current_password',
            'new_password' => 'required|confirmed',
        ]);
        $user = User::find(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();
        $request->session()->regenerate();
        return back()->with('success', 'Password changed!');
    }    

    //------------ Logout -----------------------------------------------------//

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

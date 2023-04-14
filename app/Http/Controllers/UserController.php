<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dokter;
use App\Models\Mahasiswa;
use App\Models\Paramedis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login_action(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $MahasiswaID = Mahasiswa::where('username', $request->username)->get('id')->pluck('id')->first();
        $DokterID = Dokter::where('username', $request->username)->get('id')->pluck('id')->first();
        $ParamedisID = Paramedis::where('username', $request->username)->get('id')->pluck('id')->first();

        if (isset($MahasiswaID)) {
            $User = User::where('mahasiswaid', $MahasiswaID)->get()->first();
        } elseif (isset($DokterID)) {
            $User = User::where('dokterid', $DokterID)->get()->first();
        } elseif (isset($ParamedisID)) {
            $User = User::where('paramedisid', $ParamedisID)->get()->first();
        }

        if (isset($User)) {
            if (Hash::check($request->password, $User->password)) {
                $request->session()->regenerate();
                Auth::attempt(['name' => $User->name, 'password' => $request->password]);
                if ($User->mahasiswaid) {
                    return redirect()->route('dashboard-mahasiswa');
                } elseif ($User->dokterid) {
                    return redirect(route('dashboard-dokter'));
                } elseif ($User->paramedisid) {
                    return redirect(route('dashboard-paramedis'));
                }
                return redirect()->route('login')->with("gagal", "Username dan/atau Password Salah.");
            }
            return redirect()->route('login')->with("gagal", "Username dan/atau Password Salah.");
        }
        return redirect()->route('login')->with("gagal", "Username dan/atau Password Salah.");
    }

    public function login(Request $request)
    {
        if (Auth::user()) {
            if (Auth::user()->mahasiswaid) {
                return redirect()->route('dashboard-mahasiswa');
            } elseif (Auth::user()->dokterid) {
                return redirect()->route('dashboard-dokter');
            } elseif (Auth::user()->paramedisid) {
                return redirect()->route('dashboard-paramedis');
            }
            return redirect()->route('beranda');
        }
        return view('login');
    }

    public function logout_action(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('beranda'));
    }
}
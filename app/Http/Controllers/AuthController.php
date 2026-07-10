<?php
namespace App\Http\Controllers;
use App\Models\User;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
 public function showLogin(){return view('auth.login');} public function showRegister(){return view('auth.register');}
 public function login(Request $r){$data=$r->validate(['email'=>'required|email','password'=>'required']);if(Auth::attempt($data,$r->boolean('remember'))){$r->session()->regenerate();return redirect()->intended(route('dashboard'))->with('success','Selamat datang kembali.');}return back()->withErrors(['email'=>'Email atau kata sandi salah.'])->onlyInput('email');}
 public function register(Request $r){$data=$r->validate(['name'=>'required|max:100','email'=>'required|email|unique:users','password'=>'required|min:8|confirmed']);$u=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'user']);Auth::login($u);return redirect()->route('dashboard')->with('success','Akun berhasil dibuat.');}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login')->with('success','Anda berhasil keluar.');}
}

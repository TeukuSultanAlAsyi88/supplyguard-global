<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\User;use Illuminate\Http\Request;
class UserController extends Controller { public function index(){return view('admin.users.index',['users'=>User::latest()->paginate(20)]);} public function update(Request $r,User $user){$data=$r->validate(['role'=>'required|in:admin,user','is_active'=>'nullable|boolean']);$user->update(['role'=>$data['role'],'is_active'=>$r->boolean('is_active')]);return back()->with('success','Data pengguna diperbarui.');} public function destroy(User $user){abort_if($user->id===auth()->id(),422,'Akun sendiri tidak dapat dihapus.');$user->delete();return back()->with('success','Pengguna dihapus.');} }

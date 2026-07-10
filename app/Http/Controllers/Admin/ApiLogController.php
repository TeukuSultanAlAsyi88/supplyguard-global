<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\ApiLog;
class ApiLogController extends Controller { public function index(){return view('admin.api-logs.index',['logs'=>ApiLog::latest('requested_at')->paginate(40)]);} public function clear(){ApiLog::truncate();return back()->with('success','Log API dibersihkan.');} }

<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\{User,Port,Article,ApiLog,PositiveWord,NegativeWord};
class DashboardController extends Controller { public function index(){return view('admin.dashboard',['userCount'=>User::count(),'portCount'=>Port::count(),'articleCount'=>Article::count(),'apiSuccess'=>ApiLog::where('success',true)->count(),'positiveCount'=>PositiveWord::count(),'negativeCount'=>NegativeWord::count(),'logs'=>ApiLog::latest('requested_at')->take(10)->get()]);} }

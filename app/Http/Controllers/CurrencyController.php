<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index(Request $request, CurrencyService $service)
    {
        $countries = Country::whereNotNull('currency_code')->orderBy('name')->get();
        $target = strtoupper($request->string('target', 'IDR'));
        $rate = $service->rate('USD', $target);
        $history = $service->history('USD', $target, 30);

        return view('currency.index', compact('countries', 'target', 'rate', 'history'));
    }
}

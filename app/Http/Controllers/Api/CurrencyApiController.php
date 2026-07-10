<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyApiController extends Controller
{
    public function index(Request $request, CurrencyService $service)
    {
        return response()->json(['success' => true, 'data' => $service->rate($request->string('base', 'USD'), $request->string('target', 'IDR'), $request->boolean('force'))]);
    }

    public function history(Request $request, CurrencyService $service)
    {
        return response()->json(['success' => true, 'data' => $service->history($request->string('base', 'USD'), $request->string('target', 'IDR'), $request->integer('days', 30), $request->boolean('force'))]);
    }

    public function convert(Request $request, CurrencyService $service)
    {
        $validated = $request->validate(['amount' => 'required|numeric|min:0', 'base' => 'required|size:3', 'target' => 'required|size:3']);
        return response()->json([
            'success' => true,
            'data' => [
                'amount' => (float) $validated['amount'],
                'base' => strtoupper($validated['base']),
                'target' => strtoupper($validated['target']),
                'result' => $service->convert((float) $validated['amount'], $validated['base'], $validated['target']),
            ],
        ]);
    }

    public function pairs()
    {
        return response()->json(['success' => true, 'data' => CurrencyRate::select('base_currency', 'target_currency')->distinct()->orderBy('base_currency')->get()]);
    }
}

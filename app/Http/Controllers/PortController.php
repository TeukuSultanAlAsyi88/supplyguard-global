<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::query();
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', '%'.$term.'%')
                ->orWhere('country_name', 'like', '%'.$term.'%')
                ->orWhere('city', 'like', '%'.$term.'%')
                ->orWhere('unlocode', 'like', '%'.$term.'%')
                ->orWhere('wpi_number', 'like', '%'.$term.'%'));
        }
        if ($request->filled('country')) {
            $query->where('country_name', $request->string('country'));
        }

        $ports = (clone $query)->orderBy('name')->paginate(30)->withQueryString();
        $mapPorts = (clone $query)->limit(1000)->get();
        $portCountries = Port::select('country_name')->distinct()->orderBy('country_name')->pluck('country_name');

        return view('ports.index', compact('ports', 'mapPorts', 'portCountries'));
    }
}

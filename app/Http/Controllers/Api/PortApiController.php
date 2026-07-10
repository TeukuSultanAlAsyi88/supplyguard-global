<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

class PortApiController extends Controller
{
    private function query(Request $request)
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
        return $query;
    }

    public function index(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->query($request)->orderBy('name')->paginate($request->integer('per_page', 30))]);
    }

    public function map(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->query($request)->select('id', 'name', 'unlocode', 'wpi_number', 'city', 'country_name', 'latitude', 'longitude', 'status', 'data_source')->limit($request->integer('limit', 1000))->get()]);
    }

    public function countries()
    {
        return response()->json(['success' => true, 'data' => Port::select('country_name')->distinct()->orderBy('country_name')->pluck('country_name')]);
    }

    public function show(Port $port)
    {
        return response()->json(['success' => true, 'data' => $port->load('country')]);
    }
}

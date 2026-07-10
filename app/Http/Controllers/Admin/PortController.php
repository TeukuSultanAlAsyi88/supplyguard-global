<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Port;
use App\Models\PortImportBatch;
use App\Services\PortImportService;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        return view('admin.ports.index', [
            'ports' => Port::latest()->paginate(20),
            'countries' => Country::orderBy('name')->get(),
            'imports' => PortImportBatch::with('user')->latest()->limit(10)->get(),
        ]);
    }

    public function store(Request $request)
    {
        Port::create($this->data($request));
        return back()->with('success', 'Pelabuhan ditambahkan.');
    }

    public function update(Request $request, Port $port)
    {
        $port->update($this->data($request));
        return back()->with('success', 'Pelabuhan diperbarui.');
    }

    public function destroy(Port $port)
    {
        $port->delete();
        return back()->with('success', 'Pelabuhan dihapus.');
    }

    public function sample()
    {
        $path = database_path('data/world_port_index_sample.csv');
        abort_unless(is_file($path), 404);
        return response()->download($path, 'contoh_world_port_index.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request, PortImportService $service)
    {
        $validated = $request->validate(['file' => 'required|file|mimes:csv,txt|max:20480']);
        $batch = $service->import($validated['file'], auth()->id());
        return back()->with('success', "Import selesai: {$batch->imported_rows} baris masuk, {$batch->skipped_rows} dilewati.");
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|max:150',
            'unlocode' => 'nullable|max:10',
            'wpi_number' => 'nullable|max:30',
            'city' => 'nullable|max:100',
            'country_name' => 'required|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'port_type' => 'required|max:100',
            'harbor_size' => 'nullable|max:100',
            'harbor_type' => 'nullable|max:100',
            'status' => 'required|max:50',
        ]);
        $data['data_source'] = $request->string('data_source', 'Manual')->toString();
        return $data;
    }
}

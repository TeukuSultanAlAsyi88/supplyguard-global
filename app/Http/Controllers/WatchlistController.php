<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index()
    {
        return view('watchlists.index', [
            'items' => Watchlist::with(['country.latestRisk'])->where('user_id', auth()->id())->latest()->get(),
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Country $country)
    {
        $watchlist = Watchlist::firstOrCreate(['user_id' => auth()->id(), 'country_id' => $country->id]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Negara ditambahkan ke daftar pemantauan.', 'data' => $watchlist->load('country.latestRisk')]);
        }
        return back()->with('success', 'Negara ditambahkan ke daftar pemantauan.');
    }

    public function destroy(Request $request, Watchlist $watchlist)
    {
        abort_unless($watchlist->user_id === auth()->id(), 403);
        $watchlist->delete();
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Negara dihapus dari daftar pemantauan.']);
        }
        return back()->with('success', 'Negara dihapus dari daftar pemantauan.');
    }
}

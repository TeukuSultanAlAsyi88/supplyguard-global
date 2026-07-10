<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(
        Request $request,
        NewsService $service
    ): View {
        $countries = Country::query()
            ->orderBy('name')
            ->get();

        $country = $request->filled('country')
            ? Country::find($request->integer('country'))
            : null;

        $defaultQuery =
            'logistics OR trade OR shipping OR economy '
            .'OR inflation OR export OR import';

        $query = trim(
            $request->string(
                'q',
                $defaultQuery
            )->toString()
        );

        /*
         * Ketika pengguna memilih negara, NewsService akan:
         *
         * 1. Memeriksa cache negara.
         * 2. Mengambil GNews jika cache sudah lama.
         * 3. Menyimpan berita dengan country_id.
         * 4. Menampilkan hasilnya.
         */
        $news = $service->fetch(
            country: $country,
            query: $query,
            limit: 10
        );

        $counts = collect([
            'Positif' => $news
                ->where('sentiment', 'Positif')
                ->count(),

            'Netral' => $news
                ->where('sentiment', 'Netral')
                ->count(),

            'Negatif' => $news
                ->where('sentiment', 'Negatif')
                ->count(),
        ]);

        $total = max(1, $news->count());

        $summary = $counts->map(
            fn (int $count) => [
                'count' => $count,
                'percentage' => round(
                    ($count / $total) * 100,
                    1
                ),
            ]
        );

        return view(
            'news.index',
            compact(
                'countries',
                'country',
                'query',
                'news',
                'summary'
            )
        );
    }
}
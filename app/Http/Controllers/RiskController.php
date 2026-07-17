<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use App\Services\RiskScoringService;
use App\Services\TransportationDisruptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskController extends Controller
{
    /**
     * Menampilkan halaman analisis risiko.
     */
    public function index(
        Request $request,
        RiskScoringService $riskService,
        TransportationDisruptionService $transportService
    ): View {
        $validated = $request->validate([
            'country' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],
        ]);

        $countries = Country::query()
            ->select([
                'id',
                'name',
                'code',
                'cca3',
                'flag_url',
            ])
            ->orderBy('name')
            ->get();

        $selected = null;
        $score = null;
        $transportAnalysis = null;

        if (!empty($validated['country'])) {
            $selected = Country::query()
                ->with([
                    'latestRisk',
                    'latestWeather',
                    'latestEconomic',
                ])
                ->withCount('ports')
                ->findOrFail(
                    (int) $validated['country']
                );

            /*
             * Pada halaman GET, gunakan skor terakhir agar refresh halaman
             * tidak terus-menerus menambah riwayat skor baru.
             */
            $score = $selected->latestRisk;

            /*
             * Bila negara belum pernah dihitung, lakukan perhitungan awal.
             */
            if (!$score) {
                $calculationResult =
                    $riskService->calculate(
                        $selected
                    );

                $score = $this->resolveRiskScore(
                    $selected,
                    $calculationResult
                );
            }

            $score?->loadMissing('country');

            $transportAnalysis =
                $transportService->analyze(
                    $selected
                );
        }

        $latest = RiskScore::query()
            ->with([
                'country:id,name,code,cca3,flag_url',
            ])
            ->latestCalculated()
            ->paginate(15)
            ->withQueryString();

        $latestPerCountry = RiskScore::query()
            ->latestPerCountry();

        $summary = [
            'total_calculations' =>
                RiskScore::query()->count(),

            'countries_analyzed' =>
                RiskScore::query()
                    ->distinct('country_id')
                    ->count('country_id'),

            'high_risk_countries' =>
                (clone $latestPerCountry)
                    ->highRisk()
                    ->count(),

            'average_score' => round(
                (float) (
                    (clone $latestPerCountry)
                        ->avg('total_score')
                    ?? 0
                ),
                2
            ),
        ];

        return view(
            'risk.index',
            compact(
                'countries',
                'selected',
                'score',
                'latest',
                'transportAnalysis',
                'summary'
            )
        );
    }

    /**
     * Menghitung ulang risiko negara.
     */
    public function calculate(
        Request $request,
        Country $country,
        RiskScoringService $riskService,
        TransportationDisruptionService $transportService
    ): JsonResponse|RedirectResponse {
        $calculationResult =
            $riskService->calculate(
                $country
            );

        $score = $this->resolveRiskScore(
            $country,
            $calculationResult
        );

        $score->loadMissing('country');

        $transportAnalysis =
            $transportService->analyze(
                $country
            );

        if ($request->expectsJson()) {
            $data = $score->toArray();

            /*
             * Properti tambahan tidak mengubah field lama sehingga
             * JavaScript yang telah ada tetap dapat membaca data.total_score,
             * data.risk_level, dan field komponen lainnya.
             */
            $data['risk_level_label'] =
                $score->risk_level_label;

            $data['risk_badge_class'] =
                $score->risk_badge_class;

            $data['dominant_component'] =
                $score->dominant_component;

            $data['dominant_component_label'] =
                $score->dominant_component_label;

            $data['dominant_component_score'] =
                $score->dominant_component_score;

            $data['analysis_summary'] =
                $score->analysis_summary;

            $data['decision_recommendation'] =
                $score->decision_recommendation;

            $data['transport_analysis'] =
                $transportAnalysis;

            return response()->json([
                'success' => true,
                'message' =>
                    'Skor risiko dan gangguan transportasi berhasil dihitung.',
                'data' => $data,
            ]);
        }

        return back()
            ->with(
                'success',
                'Skor risiko dan gangguan transportasi berhasil dihitung.'
            )
            ->with(
                'calculated_country_id',
                $country->id
            );
    }

    /**
     * Memastikan hasil service dikembalikan sebagai model RiskScore.
     *
     * Method ini tetap aman bila RiskScoringService mengembalikan model,
     * array, atau hanya menyimpan hasil ke database.
     */
    private function resolveRiskScore(
        Country $country,
        mixed $calculationResult
    ): RiskScore {
        if ($calculationResult instanceof RiskScore) {
            return $calculationResult;
        }

        return RiskScore::query()
            ->where(
                'country_id',
                $country->id
            )
            ->latestCalculated()
            ->firstOrFail();
    }
}
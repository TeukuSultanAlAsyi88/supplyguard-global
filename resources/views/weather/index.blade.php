@extends('layouts.app')

@section('title', 'Pemantauan Cuaca')
@section('heading', 'Pemantauan Cuaca Global')

@section('content')
@php
    $weatherValue = function ($field, $suffix = '') use ($weather) {
        if (! $weather || $weather->{$field} === null) {
            return '-';
        }

        return number_format((float) $weather->{$field}, 1, ',', '.') . $suffix;
    };

    $stormRisk = (float) ($weather?->storm_risk ?? 0);
    $rainProbability = (float) ($weather?->precipitation_probability ?? 0);
    $precipitation = (float) ($weather?->precipitation ?? 0);
    $windSpeed = (float) ($weather?->wind_speed ?? 0);
    $windGust = (float) ($weather?->wind_gust ?? 0);
    $humidity = (float) ($weather?->humidity ?? 0);
    $temperature = (float) ($weather?->temperature ?? 0);

    $riskStatus = match (true) {
        $stormRisk >= 61 => [
            'label' => 'Tinggi',
            'class' => 'badge-high',
            'impact' => 'Berisiko',
            'impact_class' => 'text-bg-danger',
        ],
        $stormRisk >= 31 => [
            'label' => 'Sedang',
            'class' => 'badge-medium',
            'impact' => 'Perlu Dipantau',
            'impact_class' => 'text-bg-warning',
        ],
        $stormRisk > 0 => [
            'label' => 'Rendah',
            'class' => 'badge-low',
            'impact' => 'Aman Terkendali',
            'impact_class' => 'text-bg-success',
        ],
        default => [
            'label' => 'Belum Tersedia',
            'class' => 'text-bg-secondary',
            'impact' => 'Menunggu Data',
            'impact_class' => 'text-bg-secondary',
        ],
    };

    $operationalRecommendations = [];

    if ($rainProbability >= 80 || $precipitation >= 20) {
        $operationalRecommendations[] = [
            'icon' => 'bi-cloud-rain',
            'title' => 'Peluang hujan tinggi',
            'text' => 'Pantau rute distribusi dan siapkan antisipasi keterlambatan pengiriman.',
        ];
    }

    if ($windSpeed >= 35 || $windGust >= 45) {
        $operationalRecommendations[] = [
            'icon' => 'bi-wind',
            'title' => 'Angin perlu dipantau',
            'text' => 'Periksa aktivitas pelabuhan, pengiriman laut, dan jalur logistik terbuka.',
        ];
    }

    if ($stormRisk >= 61) {
        $operationalRecommendations[] = [
            'icon' => 'bi-exclamation-triangle',
            'title' => 'Risiko badai tinggi',
            'text' => 'Pertimbangkan alternatif pemasok atau penjadwalan ulang distribusi.',
        ];
    } elseif ($stormRisk >= 31) {
        $operationalRecommendations[] = [
            'icon' => 'bi-shield-exclamation',
            'title' => 'Risiko badai sedang',
            'text' => 'Lakukan pemantauan berkala terhadap cuaca dan status rute pengiriman.',
        ];
    }

    if ($humidity >= 75) {
        $operationalRecommendations[] = [
            'icon' => 'bi-moisture',
            'title' => 'Kelembapan cukup tinggi',
            'text' => 'Perhatikan komoditas sensitif terhadap kelembapan selama penyimpanan.',
        ];
    }

    if (count($operationalRecommendations) === 0) {
        $operationalRecommendations[] = [
            'icon' => 'bi-check-circle',
            'title' => 'Kondisi relatif aman',
            'text' => 'Aktivitas rantai pasok dapat berjalan normal dengan pemantauan berkala.',
        ];
    }

    $forecastRows = [
        [
            'time' => 'Saat ini',
            'condition' => $weather?->condition ?? '-',
            'temperature' => $weatherValue('temperature', '°C'),
            'rain' => $weatherValue('precipitation_probability', '%'),
            'wind' => $weatherValue('wind_speed', ' km/jam'),
        ],
        [
            'time' => '+6 jam',
            'condition' => $weather?->condition ?? '-',
            'temperature' => $weather ? number_format(max(0, $temperature - 1), 1, ',', '.') . '°C' : '-',
            'rain' => $weather ? number_format(max(0, min(100, $rainProbability - 10)), 1, ',', '.') . '%' : '-',
            'wind' => $weather ? number_format(max(0, $windSpeed + 2), 1, ',', '.') . ' km/jam' : '-',
        ],
        [
            'time' => '+12 jam',
            'condition' => $weather?->condition ?? '-',
            'temperature' => $weather ? number_format(max(0, $temperature - 2), 1, ',', '.') . '°C' : '-',
            'rain' => $weather ? number_format(max(0, min(100, $rainProbability - 20)), 1, ',', '.') . '%' : '-',
            'wind' => $weather ? number_format(max(0, $windSpeed + 1), 1, ',', '.') . ' km/jam' : '-',
        ],
        [
            'time' => '+24 jam',
            'condition' => $weather?->condition ?? '-',
            'temperature' => $weather ? number_format(max(0, $temperature - 1.5), 1, ',', '.') . '°C' : '-',
            'rain' => $weather ? number_format(max(0, min(100, $rainProbability - 30)), 1, ',', '.') . '%' : '-',
            'wind' => $weather ? number_format(max(0, $windSpeed), 1, ',', '.') . ' km/jam' : '-',
        ],
    ];

    $selectedCountriesMap = $countries
        ->mapWithKeys(function ($c) {
            return [
                (string) $c->id => [
                    'latitude' => $c->latitude,
                    'longitude' => $c->longitude,
                ],
            ];
        })
        ->toArray();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="page-title">
            Cuaca Global
        </h2>

        <p class="text-muted mb-0">
            Pantau temperatur, hujan, angin kencang, dan risiko badai tanpa memuat ulang halaman.
        </p>
    </div>

    <span class="badge text-bg-light border">
        <i class="bi bi-broadcast me-1"></i>
        Sumber: Open-Meteo
    </span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form
            id="weatherForm"
            class="row g-3 align-items-end"
        >
            <div class="col-lg-9">
                <label class="form-label">
                    Negara
                </label>

                <select
                    id="countrySelect"
                    class="form-select"
                    required
                >
                    <option value="">
                        Pilih negara
                    </option>

                    @foreach($countries as $c)
                        <option
                            value="{{ $c->id }}"
                            @selected($country?->id === $c->id)
                        >
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 d-grid">
                <button class="btn btn-primary">
                    <i class="bi bi-cloud-download me-1"></i>
                    Perbarui Cuaca
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach([
        ['temperature', 'Temperatur', '°C', 'bi-thermometer-half'],
        ['apparent_temperature', 'Terasa Seperti', '°C', 'bi-person-arms-up'],
        ['precipitation', 'Curah Hujan', ' mm', 'bi-cloud-rain'],
        ['precipitation_probability', 'Peluang Hujan', '%', 'bi-droplet'],
        ['wind_speed', 'Kecepatan Angin', ' km/jam', 'bi-wind'],
        ['wind_gust', 'Hembusan Angin', ' km/jam', 'bi-tornado'],
        ['humidity', 'Kelembapan', '%', 'bi-moisture'],
        ['storm_risk', 'Risiko Badai', ' / 100', 'bi-exclamation-triangle'],
    ] as $card)
        <div class="col-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-muted small">
                            {{ $card[1] }}
                        </div>

                        <i class="bi {{ $card[3] }} text-primary"></i>
                    </div>

                    <div
                        class="fs-3 fw-bold mt-2"
                        id="metric-{{ $card[0] }}"
                    >
                        {{
                            $weather && $weather->{$card[0]} !== null
                                ? number_format((float) $weather->{$card[0]}, 1, ',', '.') . $card[2]
                                : '-'
                        }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                <div>
                    <h5 class="mb-1">
                        Peta Risiko Cuaca
                    </h5>

                    <small class="text-muted">
                        Lingkaran menunjukkan tingkat risiko badai dari negara yang telah disinkronkan.
                    </small>
                </div>

                <span
                    id="weatherCondition"
                    class="badge text-bg-secondary align-self-start"
                >
                    {{ $weather?->condition ?? 'Belum tersedia' }}
                </span>
            </div>

            <div class="card-body pt-2">
                <div
                    id="weatherMap"
                    class="map"
                ></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="d-grid gap-4">

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h5 class="mb-1">
                                Ringkasan Kondisi Saat Ini
                            </h5>

                            <small class="text-muted">
                                Informasi cuaca aktif dari negara yang dipilih.
                            </small>
                        </div>

                        <span
                            id="summaryImpactBadge"
                            class="badge {{ $riskStatus['impact_class'] }}"
                        >
                            {{ $riskStatus['impact'] }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mt-4">
                        <div
                            class="rounded-4 d-grid"
                            style="
                                width: 58px;
                                height: 58px;
                                place-items: center;
                                background: rgba(13, 110, 253, 0.12);
                                border: 1px solid rgba(13, 110, 253, 0.2);
                            "
                        >
                            <i
                                class="bi bi-cloud-sun text-primary"
                                style="font-size: 1.7rem;"
                            ></i>
                        </div>

                        <div>
                            <div
                                id="summaryTemperature"
                                class="fs-2 fw-bold lh-1"
                            >
                                {{ $weatherValue('temperature', '°C') }}
                            </div>

                            <div
                                id="summaryCondition"
                                class="text-muted mt-1"
                            >
                                {{ $weather?->condition ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <dl class="row mb-0 small">
                        <dt class="col-6 text-muted">
                            Negara aktif
                        </dt>
                        <dd
                            id="activeCountry"
                            class="col-6 text-end"
                        >
                            {{ $country?->name ?? '-' }}
                        </dd>

                        <dt class="col-6 text-muted">
                            Pembaruan
                        </dt>
                        <dd
                            id="observedAt"
                            class="col-6 text-end"
                        >
                            {{ $weather?->observed_at?->format('d/m/Y H:i') ?? '-' }}
                        </dd>

                        <dt class="col-6 text-muted">
                            Kondisi
                        </dt>
                        <dd
                            id="conditionText"
                            class="col-6 text-end"
                        >
                            {{ $weather?->condition ?? '-' }}
                        </dd>

                        <dt class="col-6 text-muted">
                            Sumber data
                        </dt>
                        <dd class="col-6 text-end">
                            Open-Meteo
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5>
                        Legenda Risiko
                    </h5>

                    <div class="d-grid gap-3 mt-3">
                        <div>
                            <span class="badge badge-low me-2">
                                Rendah
                            </span>
                            0–30
                        </div>

                        <div>
                            <span class="badge badge-medium me-2">
                                Sedang
                            </span>
                            31–60
                        </div>

                        <div>
                            <span class="badge badge-high me-2">
                                Tinggi
                            </span>
                            61–100
                        </div>
                    </div>

                    <hr>

                    <div class="small text-muted">
                        Semakin besar nilai risiko badai, semakin tinggi potensi gangguan terhadap aktivitas logistik.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1">
                        Prakiraan 24 Jam
                    </h5>

                    <small class="text-muted">
                        Ringkasan kondisi sebagai acuan pemantauan operasional.
                    </small>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Suhu</th>
                                    <th>Hujan</th>
                                    <th>Angin</th>
                                </tr>
                            </thead>

                            <tbody id="forecastBody">
                                @foreach($forecastRows as $row)
                                    <tr>
                                        <td>
                                            {{ $row['time'] }}
                                        </td>

                                        <td>
                                            {{ $row['temperature'] }}
                                        </td>

                                        <td>
                                            {{ $row['rain'] }}
                                        </td>

                                        <td>
                                            {{ $row['wind'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 small">
                        Data ini digunakan sebagai gambaran cepat. Untuk prakiraan lebih detail, lakukan pembaruan data secara berkala.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1">
                        Insight & Rekomendasi
                    </h5>

                    <small class="text-muted">
                        Dampak cuaca terhadap rantai pasok.
                    </small>

                    <div
                        id="weatherInsightList"
                        class="d-grid gap-3 mt-3"
                    >
                        @foreach($operationalRecommendations as $recommendation)
                            <div class="d-flex gap-3">
                                <div>
                                    <span
                                        class="rounded-circle d-grid"
                                        style="
                                            width: 34px;
                                            height: 34px;
                                            place-items: center;
                                            background: rgba(13, 110, 253, 0.12);
                                            color: #0d6efd;
                                        "
                                    >
                                        <i class="bi {{ $recommendation['icon'] }}"></i>
                                    </span>
                                </div>

                                <div>
                                    <div class="fw-semibold small">
                                        {{ $recommendation['title'] }}
                                    </div>

                                    <div class="text-muted small mt-1">
                                        {{ $recommendation['text'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-info mt-4 mb-0 small">
                        Peta global akan semakin lengkap setelah command sinkronisasi cuaca dijalankan secara berkala.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const initialWeather = @json($weather);
const initialCountry = @json($country);
const overview = @json($mapWeather);
const selectedCountriesMap = @json($selectedCountriesMap);

const map = L.map('weatherMap').setView([5, 110], 2);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '© OpenStreetMap contributors'
    }
).addTo(map);

const globalLayer = L.layerGroup().addTo(map);

let selectedLayer = null;

function riskColor(score) {
    return score <= 30
        ? '#16a34a'
        : (
            score <= 60
                ? '#d97706'
                : '#dc2626'
        );
}

function impactStatus(score) {
    score = Number(score || 0);

    if (score >= 61) {
        return {
            label: 'Berisiko',
            className: 'text-bg-danger'
        };
    }

    if (score >= 31) {
        return {
            label: 'Perlu Dipantau',
            className: 'text-bg-warning'
        };
    }

    if (score > 0) {
        return {
            label: 'Aman Terkendali',
            className: 'text-bg-success'
        };
    }

    return {
        label: 'Menunggu Data',
        className: 'text-bg-secondary'
    };
}

function plotOverview(items) {
    globalLayer.clearLayers();

    (items || []).forEach(w => {
        const c = w.country;

        if (
            !c ||
            c.latitude === null ||
            c.longitude === null
        ) {
            return;
        }

        const score = Number(w.storm_risk || 0);

        L.circleMarker(
            [c.latitude, c.longitude],
            {
                radius: 7 + score / 16,
                color: riskColor(score),
                fillColor: riskColor(score),
                fillOpacity: .68,
                weight: 2
            }
        )
            .bindPopup(`
                <strong>${sg.escape(c.name)}</strong><br>
                Kondisi: ${sg.escape(w.condition || '-')}<br>
                Hujan: ${sg.number(w.precipitation, 1)} mm<br>
                Angin: ${sg.number(w.wind_speed, 1)} km/jam<br>
                Risiko badai: ${sg.number(score, 1)}/100
            `)
            .addTo(globalLayer);
    });
}

function forecastRowsFromWeather(w) {
    if (!w) {
        return [
            ['Saat ini', '-', '-', '-'],
            ['+6 jam', '-', '-', '-'],
            ['+12 jam', '-', '-', '-'],
            ['+24 jam', '-', '-', '-']
        ];
    }

    const temperature = Number(w.temperature || 0);
    const rain = Number(w.precipitation_probability || 0);
    const wind = Number(w.wind_speed || 0);

    return [
        [
            'Saat ini',
            `${sg.number(temperature, 1)}°C`,
            `${sg.number(rain, 1)}%`,
            `${sg.number(wind, 1)} km/jam`
        ],
        [
            '+6 jam',
            `${sg.number(Math.max(0, temperature - 1), 1)}°C`,
            `${sg.number(Math.max(0, Math.min(100, rain - 10)), 1)}%`,
            `${sg.number(Math.max(0, wind + 2), 1)} km/jam`
        ],
        [
            '+12 jam',
            `${sg.number(Math.max(0, temperature - 2), 1)}°C`,
            `${sg.number(Math.max(0, Math.min(100, rain - 20)), 1)}%`,
            `${sg.number(Math.max(0, wind + 1), 1)} km/jam`
        ],
        [
            '+24 jam',
            `${sg.number(Math.max(0, temperature - 1.5), 1)}°C`,
            `${sg.number(Math.max(0, Math.min(100, rain - 30)), 1)}%`,
            `${sg.number(Math.max(0, wind), 1)} km/jam`
        ]
    ];
}

function insightItemsFromWeather(w) {
    if (!w) {
        return [
            {
                icon: 'bi-info-circle',
                title: 'Data belum tersedia',
                text: 'Pilih negara lalu perbarui cuaca untuk menampilkan rekomendasi.'
            }
        ];
    }

    const items = [];

    const rainProbability = Number(w.precipitation_probability || 0);
    const precipitation = Number(w.precipitation || 0);
    const windSpeed = Number(w.wind_speed || 0);
    const windGust = Number(w.wind_gust || 0);
    const humidity = Number(w.humidity || 0);
    const stormRisk = Number(w.storm_risk || 0);

    if (rainProbability >= 80 || precipitation >= 20) {
        items.push({
            icon: 'bi-cloud-rain',
            title: 'Peluang hujan tinggi',
            text: 'Pantau rute distribusi dan siapkan antisipasi keterlambatan pengiriman.'
        });
    }

    if (windSpeed >= 35 || windGust >= 45) {
        items.push({
            icon: 'bi-wind',
            title: 'Angin perlu dipantau',
            text: 'Periksa aktivitas pelabuhan, pengiriman laut, dan jalur logistik terbuka.'
        });
    }

    if (stormRisk >= 61) {
        items.push({
            icon: 'bi-exclamation-triangle',
            title: 'Risiko badai tinggi',
            text: 'Pertimbangkan alternatif pemasok atau penjadwalan ulang distribusi.'
        });
    } else if (stormRisk >= 31) {
        items.push({
            icon: 'bi-shield-exclamation',
            title: 'Risiko badai sedang',
            text: 'Lakukan pemantauan berkala terhadap cuaca dan status rute pengiriman.'
        });
    }

    if (humidity >= 75) {
        items.push({
            icon: 'bi-moisture',
            title: 'Kelembapan cukup tinggi',
            text: 'Perhatikan komoditas sensitif terhadap kelembapan selama penyimpanan.'
        });
    }

    if (items.length === 0) {
        items.push({
            icon: 'bi-check-circle',
            title: 'Kondisi relatif aman',
            text: 'Aktivitas rantai pasok dapat berjalan normal dengan pemantauan berkala.'
        });
    }

    return items;
}

function renderForecast(w) {
    const tbody = document.getElementById('forecastBody');

    if (!tbody) {
        return;
    }

    const rows = forecastRowsFromWeather(w);

    tbody.innerHTML = rows.map(row => `
        <tr>
            <td>${sg.escape(row[0])}</td>
            <td>${sg.escape(row[1])}</td>
            <td>${sg.escape(row[2])}</td>
            <td>${sg.escape(row[3])}</td>
        </tr>
    `).join('');
}

function renderInsights(w) {
    const wrapper = document.getElementById('weatherInsightList');

    if (!wrapper) {
        return;
    }

    const items = insightItemsFromWeather(w);

    wrapper.innerHTML = items.map(item => `
        <div class="d-flex gap-3">
            <div>
                <span
                    class="rounded-circle d-grid"
                    style="
                        width: 34px;
                        height: 34px;
                        place-items: center;
                        background: rgba(13, 110, 253, 0.12);
                        color: #0d6efd;
                    "
                >
                    <i class="bi ${sg.escape(item.icon)}"></i>
                </span>
            </div>

            <div>
                <div class="fw-semibold small">
                    ${sg.escape(item.title)}
                </div>

                <div class="text-muted small mt-1">
                    ${sg.escape(item.text)}
                </div>
            </div>
        </div>
    `).join('');
}

function renderWeather(w, c) {
    const suffix = {
        temperature: '°C',
        apparent_temperature: '°C',
        precipitation: ' mm',
        precipitation_probability: '%',
        wind_speed: ' km/jam',
        wind_gust: ' km/jam',
        humidity: '%',
        storm_risk: ' / 100'
    };

    Object.keys(suffix).forEach(k => {
        const element = document.getElementById(`metric-${k}`);

        if (!element) {
            return;
        }

        element.textContent =
            w?.[k] === null || w?.[k] === undefined
                ? '-'
                : `${sg.number(w[k], 1)}${suffix[k]}`;
    });

    const status = impactStatus(w?.storm_risk);

    const impactBadge = document.getElementById('summaryImpactBadge');

    if (impactBadge) {
        impactBadge.textContent = status.label;
        impactBadge.className = `badge ${status.className}`;
    }

    const activeCountry = document.getElementById('activeCountry');
    const observedAt = document.getElementById('observedAt');
    const conditionText = document.getElementById('conditionText');
    const weatherCondition = document.getElementById('weatherCondition');
    const summaryTemperature = document.getElementById('summaryTemperature');
    const summaryCondition = document.getElementById('summaryCondition');

    if (activeCountry) {
        activeCountry.textContent = c?.name || '-';
    }

    if (observedAt) {
        observedAt.textContent =
            w?.observed_at
                ? new Date(w.observed_at).toLocaleString('id-ID')
                : '-';
    }

    if (conditionText) {
        conditionText.textContent = w?.condition || '-';
    }

    if (weatherCondition) {
        weatherCondition.textContent = w?.condition || 'Tidak tersedia';
    }

    if (summaryTemperature) {
        summaryTemperature.textContent =
            w?.temperature === null || w?.temperature === undefined
                ? '-'
                : `${sg.number(w.temperature, 1)}°C`;
    }

    if (summaryCondition) {
        summaryCondition.textContent = w?.condition || '-';
    }

    renderForecast(w);
    renderInsights(w);

    if (selectedLayer) {
        map.removeLayer(selectedLayer);
    }

    if (
        c &&
        c.latitude !== null &&
        c.longitude !== null
    ) {
        selectedLayer = L.marker(
            [c.latitude, c.longitude]
        )
            .addTo(map)
            .bindPopup(`
                <strong>${sg.escape(c.name)}</strong><br>
                ${sg.escape(w?.condition || '-')}<br>
                Risiko: ${sg.number(w?.storm_risk, 1)}/100
            `)
            .openPopup();

        map.setView(
            [c.latitude, c.longitude],
            5
        );
    }
}

plotOverview(overview);
renderWeather(initialWeather, initialCountry);

document
    .getElementById('weatherForm')
    .addEventListener('submit', async e => {
        e.preventDefault();

        const id =
            document.getElementById('countrySelect').value;

        if (!id) {
            return;
        }

        sg.loading(true);

        try {
            const payload =
                await sg.fetchJson(
                    `{{ url('/api/weather') }}/${id}?force=1`
                );

            const option =
                document.querySelector(
                    `#countrySelect option[value="${id}"]`
                );

            const country = {
                id: Number(id),
                name: option ? option.textContent : '-'
            };

            Object.assign(
                country,
                selectedCountriesMap[id] || {}
            );

            renderWeather(
                payload.data,
                country
            );

            sg.toast(
                'Data cuaca berhasil diperbarui.'
            );

            history.replaceState(
                {},
                '',
                `{{ route('weather.index') }}?country=${id}`
            );
        } catch (err) {
            sg.toast(
                err.message,
                false
            );
        } finally {
            sg.loading(false);
        }
    });
</script>
@endpush
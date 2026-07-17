@extends('layouts.app')

@section('title', 'Data Negara')
@section('heading', 'Data Negara')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="page-title mb-1">Negara Dunia</h2>
        <p class="text-muted mb-0">
            Data negara dari REST Countries dengan pencarian cepat.
        </p>
    </div>

    <form method="POST" action="{{ route('countries.sync') }}">
        @csrf

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-arrow-repeat me-1"></i>
            Sinkronkan Negara
        </button>
    </form>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form id="countrySearchForm" class="row g-3">
            <div class="col-md-7">
                <input
                    id="countryQuery"
                    type="search"
                    class="form-control"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama, ISO2, atau ISO3..."
                    autocomplete="off"
                >
            </div>

            <div class="col-md-3">
                <select
                    id="countryRegion"
                    class="form-select"
                    name="region"
                >
                    <option value="">Semua wilayah</option>

                    @foreach($regions as $region)
                        <option
                            value="{{ $region }}"
                            @selected(request('region') === $region)
                        >
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>
                    Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Negara</th>
                    <th>Kode</th>
                    <th>Wilayah</th>
                    <th>Mata Uang</th>
                    <th>Populasi</th>
                    <th>Risiko</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="countryRows">
                @forelse($countries as $country)
                    @php
                        $latestRisk = $country->latestRisk;

                        $riskClass = $latestRisk
                            ? (
                                $latestRisk->risk_level === 'Rendah'
                                    ? 'badge-low'
                                    : (
                                        $latestRisk->risk_level === 'Sedang'
                                            ? 'badge-medium'
                                            : 'badge-high'
                                    )
                            )
                            : null;
                    @endphp

                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img
                                    src="{{ $country->flag_url }}"
                                    class="flag"
                                    alt="Bendera {{ $country->name }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'"
                                >

                                <div>
                                    <strong>{{ $country->name }}</strong>

                                    <div class="small text-muted">
                                        {{ $country->capital ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $country->code ?: '-' }}
                            /
                            {{ $country->cca3 ?: '-' }}
                        </td>

                        <td>{{ $country->region ?: '-' }}</td>
                        <td>{{ $country->currency_code ?: '-' }}</td>

                        <td>
                            {{
                                $country->population
                                    ? number_format(
                                        $country->population,
                                        0,
                                        ',',
                                        '.'
                                    )
                                    : '-'
                            }}
                        </td>

                        <td>
                            @if($latestRisk)
                                <span class="badge {{ $riskClass }}">
                                    {{ $latestRisk->risk_level }}
                                </span>
                            @else
                                <span class="text-muted">
                                    Belum dihitung
                                </span>
                            @endif
                        </td>

                        <td>
                            <a
                                class="btn btn-sm btn-outline-primary"
                                href="{{ route('countries.show', $country) }}"
                            >
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="7"
                            class="text-center py-5 text-muted"
                        >
                            Data negara belum tersedia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="serverPagination" class="card-footer">
        {{ $countries->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const countryForm =
            document.getElementById('countrySearchForm');

        const countryRows =
            document.getElementById('countryRows');

        const serverPagination =
            document.getElementById('serverPagination');

        if (!countryForm || !countryRows) {
            return;
        }

        function renderRiskBadge(risk) {
            if (!risk) {
                return `
                    <span class="text-muted">
                        Belum dihitung
                    </span>
                `;
            }

            return `
                <span class="badge ${sg.riskClass(risk.risk_level)}">
                    ${sg.escape(risk.risk_level ?? '-')}
                </span>
            `;
        }

        function renderCountryRow(country) {
            const countryId =
                Number(country.id || 0);

            const detailUrl =
                `{{ url('/negara') }}/${countryId}`;

            const population =
                country.population
                    ? sg.number(country.population)
                    : '-';

            return `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img
                                src="${sg.escape(country.flag_url || '')}"
                                class="flag"
                                alt="Bendera ${sg.escape(country.name ?? '')}"
                                loading="lazy"
                                onerror="this.style.display='none'"
                            >

                            <div>
                                <strong>
                                    ${sg.escape(country.name ?? '-')}
                                </strong>

                                <div class="small text-muted">
                                    ${sg.escape(country.capital || '-')}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        ${sg.escape(country.code || '-')}
                        /
                        ${sg.escape(country.cca3 || '-')}
                    </td>

                    <td>${sg.escape(country.region || '-')}</td>
                    <td>${sg.escape(country.currency_code || '-')}</td>
                    <td>${population}</td>

                    <td>
                        ${renderRiskBadge(country.latest_risk)}
                    </td>

                    <td>
                        <a
                            class="btn btn-sm btn-outline-primary"
                            href="${detailUrl}"
                        >
                            Detail
                        </a>
                    </td>
                </tr>
            `;
        }

        countryForm.addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();

                const params =
                    new URLSearchParams(
                        new FormData(countryForm)
                    );

                params.set('per_page', '100');

                sg.loading(true);

                try {
                    const payload =
                        await sg.fetchJson(
                            `{{ url('/api/countries') }}?${params.toString()}`
                        );

                    const rows =
                        payload?.data?.data
                        ?? payload?.data
                        ?? [];

                    serverPagination
                        ?.classList
                        .add('d-none');

                    countryRows.innerHTML =
                        rows.length
                            ? rows
                                .map(renderCountryRow)
                                .join('')
                            : `
                                <tr>
                                    <td
                                        colspan="7"
                                        class="text-center py-5 text-muted"
                                    >
                                        Negara tidak ditemukan.
                                    </td>
                                </tr>
                            `;

                    history.replaceState(
                        {},
                        '',
                        `{{ route('countries.index') }}?${params.toString()}`
                    );
                } catch (error) {
                    console.error(error);

                    sg.toast(
                        error.message
                            ?? 'Pencarian negara gagal.',
                        false
                    );
                } finally {
                    sg.loading(false);
                }
            }
        );
    }
);
</script>
@endpush
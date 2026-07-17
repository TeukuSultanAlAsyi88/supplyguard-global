@extends('layouts.app')

@section('title', 'Lokasi Pelabuhan')
@section('heading', 'Lokasi Pelabuhan Dunia')

@php
    $safeMapPorts = $mapPorts->values();

    $safeMapCenter = $mapCenter ?? [
        'latitude' => 20,
        'longitude' => 0,
        'zoom' => 2,
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="page-title mb-1">
            Peta Pelabuhan Global
        </h2>

        <p class="text-muted mb-0">
            Cari pelabuhan berdasarkan nama, kota, negara, UN/LOCODE,
            atau nomor World Port Index.
        </p>
    </div>

    <div class="small text-muted text-lg-end">
        <div>
            Data terakhir diperbarui:
        </div>

        <strong class="text-body">
            {{
                $latestImportedAt
                    ? \Illuminate\Support\Carbon::parse($latestImportedAt)
                        ->format('d M Y, H:i')
                    : 'Belum tersedia'
            }}
        </strong>
    </div>
</div>

{{-- =====================================================
     RINGKASAN DATA PELABUHAN
===================================================== --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Total Pelabuhan
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{ number_format($statistics['total_ports'] ?? 0, 0, ',', '.') }}
                    </h3>

                    <i class="bi bi-buildings fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Hasil Pencarian
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 id="filteredPortCount" class="mb-0">
                        {{ number_format($statistics['filtered_ports'] ?? 0, 0, ',', '.') }}
                    </h3>

                    <i class="bi bi-search fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Marker pada Peta
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 id="portCount" class="mb-0">
                        {{ number_format($statistics['mapped_ports'] ?? 0, 0, ',', '.') }}
                    </h3>

                    <i class="bi bi-geo-alt fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-2">
                    Negara Tersedia
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="mb-0">
                        {{ number_format($statistics['countries'] ?? 0, 0, ',', '.') }}
                    </h3>

                    <i class="bi bi-globe2 fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================
     FORM PENCARIAN
===================================================== --}}
<div class="card mb-4">
    <div class="card-body">
        <form
            id="portForm"
            method="GET"
            action="{{ route('ports.index') }}"
            class="row g-3 align-items-end"
        >
            <div class="col-lg-5">
                <label for="portQuery" class="form-label">
                    Pencarian
                </label>

                <input
                    id="portQuery"
                    type="search"
                    name="q"
                    class="form-control"
                    value="{{ $keyword ?? request('q') }}"
                    placeholder="Contoh: Tanjung Priok, Indonesia, IDTPP..."
                    autocomplete="off"
                >
            </div>

            <div class="col-lg-4">
                <label for="portCountry" class="form-label">
                    Filter Negara
                </label>

                <select
                    id="portCountry"
                    name="country"
                    class="form-select"
                >
                    <option value="">
                        Semua negara
                    </option>

                    @foreach($portCountries as $name)
                        <option
                            value="{{ $name }}"
                            @selected(($selectedCountry ?? request('country')) === $name)
                        >
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-8 col-lg-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>
                    Cari
                </button>
            </div>

            <div class="col-sm-4 col-lg-1 d-grid">
                <a
                    href="{{ route('ports.index') }}"
                    class="btn btn-outline-secondary"
                    title="Reset pencarian"
                    aria-label="Reset pencarian"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================
     PETA PELABUHAN
===================================================== --}}
<div class="card mb-4">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="mb-1">
                Sebaran Pelabuhan
            </h5>

            <p class="small text-muted mb-0">
                Klik marker untuk melihat informasi pelabuhan.
            </p>
        </div>

        <div class="small text-muted">
            Maksimal 1.000 marker
        </div>
    </div>

    <div class="card-body p-2">
        <div
            id="portsMap"
            class="map"
            role="region"
            aria-label="Peta lokasi pelabuhan dunia"
        ></div>
    </div>
</div>

{{-- =====================================================
     TABEL PELABUHAN
===================================================== --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-1">
            Daftar Pelabuhan
        </h5>

        <p class="small text-muted mb-0">
            Informasi lokasi, kode, jenis, sumber data, dan status pelabuhan.
        </p>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Pelabuhan</th>
                    <th>Kode</th>
                    <th>Lokasi</th>
                    <th>Jenis / Ukuran</th>
                    <th>Sumber</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody id="portRows">
                @forelse($ports as $port)
                    @php
                        $status = strtolower(trim((string) $port->status));

                        $statusClass = in_array(
                            $status,
                            ['active', 'aktif', 'open', 'operational'],
                            true
                        )
                            ? 'text-bg-success'
                            : (
                                in_array(
                                    $status,
                                    ['closed', 'ditutup', 'inactive', 'nonaktif'],
                                    true
                                )
                                    ? 'text-bg-danger'
                                    : 'text-bg-secondary'
                            );
                    @endphp

                    <tr>
                        <td>
                            <strong>
                                {{ $port->name }}
                            </strong>

                            <div class="small text-muted">
                                WPI: {{ $port->wpi_number ?: '-' }}
                            </div>
                        </td>

                        <td>
                            {{ $port->unlocode ?: '-' }}
                        </td>

                        <td>
                            <div>
                                {{ $port->city ?: '-' }}
                            </div>

                            <div class="small text-muted">
                                {{ $port->country?->name ?: ($port->country_name ?: '-') }}
                            </div>
                        </td>

                        <td>
                            <div>
                                {{ $port->type_label }}
                            </div>

                            <div class="small text-muted">
                                {{ $port->harbor_size ?: 'Ukuran tidak tersedia' }}
                            </div>
                        </td>

                        <td>
                            <span class="badge text-bg-secondary">
                                {{ $port->data_source ?: 'Manual' }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $statusClass }}">
                                {{ $port->status_label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-muted py-5"
                        >
                            Pelabuhan tidak ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="serverPagination" class="card-footer">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="small text-muted">
                Menampilkan maksimal 30 data per halaman.
                Status operasional mengikuti data yang tersimpan dan bukan
                data kemacetan pelabuhan secara real-time.
            </div>

            <div>
                {{ $ports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const portForm =
        document.getElementById('portForm');

    const portQuery =
        document.getElementById('portQuery');

    const portCountry =
        document.getElementById('portCountry');

    const portRows =
        document.getElementById('portRows');

    const portCount =
        document.getElementById('portCount');

    const filteredPortCount =
        document.getElementById('filteredPortCount');

    const serverPagination =
        document.getElementById('serverPagination');

    const initialMapPorts =
        @json($safeMapPorts);

    const mapCenter =
        @json($safeMapCenter);

    const map = L.map('portsMap', {
        worldCopyJump: true,
        preferCanvas: true,
    }).setView(
        [
            Number(mapCenter.latitude || 20),
            Number(mapCenter.longitude || 0),
        ],
        Number(mapCenter.zoom || 2)
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution:
                '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }
    ).addTo(map);

    const markerLayer =
        L.layerGroup().addTo(map);

    function countryName(port) {
        return (
            port?.country?.name
            || port?.country_name
            || '-'
        );
    }

    function portCode(port) {
        return (
            port?.unlocode
            || port?.wpi_number
            || '-'
        );
    }

    function normalizeStatus(status) {
        return String(status || '')
            .trim()
            .toLowerCase();
    }

    function statusLabel(status) {
        const normalized =
            normalizeStatus(status);

        if (!normalized) {
            return 'Tidak tersedia';
        }

        return normalized
            .replaceAll('_', ' ')
            .replaceAll('-', ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function statusClass(status) {
        const normalized =
            normalizeStatus(status);

        if (
            [
                'active',
                'aktif',
                'open',
                'operational',
            ].includes(normalized)
        ) {
            return 'text-bg-success';
        }

        if (
            [
                'closed',
                'ditutup',
                'inactive',
                'nonaktif',
            ].includes(normalized)
        ) {
            return 'text-bg-danger';
        }

        return 'text-bg-secondary';
    }

    function typeLabel(type) {
        const normalized =
            String(type || '').trim();

        if (!normalized) {
            return 'Tidak tersedia';
        }

        return normalized
            .replaceAll('_', ' ')
            .replaceAll('-', ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function validCoordinate(latitude, longitude) {
        const lat = Number(latitude);
        const lng = Number(longitude);

        return Number.isFinite(lat)
            && Number.isFinite(lng)
            && lat >= -90
            && lat <= 90
            && lng >= -180
            && lng <= 180;
    }

    function renderMap(items, fitMap = true) {
        markerLayer.clearLayers();

        const bounds = [];

        items.forEach(function (port) {
            if (
                !validCoordinate(
                    port.latitude,
                    port.longitude
                )
            ) {
                return;
            }

            const latitude =
                Number(port.latitude);

            const longitude =
                Number(port.longitude);

            const detailUrl =
                port.country_id
                    ? `{{ url('/negara') }}/${Number(port.country_id)}`
                    : null;

            const popup = `
                <div style="min-width: 220px">
                    <strong>
                        ${sg.escape(port.name || '-')}
                    </strong>

                    <div class="mt-1">
                        ${sg.escape(port.city || '-')}
                        ·
                        ${sg.escape(countryName(port))}
                    </div>

                    <hr class="my-2">

                    <div>
                        UN/LOCODE:
                        <strong>
                            ${sg.escape(port.unlocode || '-')}
                        </strong>
                    </div>

                    <div>
                        WPI:
                        <strong>
                            ${sg.escape(port.wpi_number || '-')}
                        </strong>
                    </div>

                    <div>
                        Jenis:
                        <strong>
                            ${sg.escape(typeLabel(port.port_type))}
                        </strong>
                    </div>

                    <div>
                        Status:
                        <strong>
                            ${sg.escape(statusLabel(port.status))}
                        </strong>
                    </div>

                    ${
                        detailUrl
                            ? `
                                <a
                                    href="${detailUrl}"
                                    class="btn btn-sm btn-primary mt-3"
                                >
                                    Lihat Detail Negara
                                </a>
                            `
                            : ''
                    }
                </div>
            `;

            L.marker([
                latitude,
                longitude,
            ])
                .bindPopup(popup)
                .addTo(markerLayer);

            bounds.push([
                latitude,
                longitude,
            ]);
        });

        portCount.textContent =
            new Intl.NumberFormat('id-ID')
                .format(bounds.length);

        if (!fitMap || bounds.length === 0) {
            return;
        }

        if (bounds.length === 1) {
            map.setView(
                bounds[0],
                7
            );

            return;
        }

        map.fitBounds(
            bounds,
            {
                padding: [
                    30,
                    30,
                ],
                maxZoom: 6,
            }
        );
    }

    function renderRows(items) {
        if (!items.length) {
            portRows.innerHTML = `
                <tr>
                    <td
                        colspan="6"
                        class="text-center text-muted py-5"
                    >
                        Pelabuhan tidak ditemukan.
                    </td>
                </tr>
            `;

            return;
        }

        portRows.innerHTML =
            items.map(function (port) {
                return `
                    <tr>
                        <td>
                            <strong>
                                ${sg.escape(port.name || '-')}
                            </strong>

                            <div class="small text-muted">
                                WPI:
                                ${sg.escape(port.wpi_number || '-')}
                            </div>
                        </td>

                        <td>
                            ${sg.escape(port.unlocode || '-')}
                        </td>

                        <td>
                            <div>
                                ${sg.escape(port.city || '-')}
                            </div>

                            <div class="small text-muted">
                                ${sg.escape(countryName(port))}
                            </div>
                        </td>

                        <td>
                            <div>
                                ${sg.escape(typeLabel(port.port_type))}
                            </div>

                            <div class="small text-muted">
                                ${sg.escape(
                                    port.harbor_size
                                    || 'Ukuran tidak tersedia'
                                )}
                            </div>
                        </td>

                        <td>
                            <span class="badge text-bg-secondary">
                                ${sg.escape(port.data_source || 'Manual')}
                            </span>
                        </td>

                        <td>
                            <span class="badge ${statusClass(port.status)}">
                                ${sg.escape(statusLabel(port.status))}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
    }

    function extractMapItems(payload) {
        if (Array.isArray(payload?.data)) {
            return payload.data;
        }

        if (Array.isArray(payload)) {
            return payload;
        }

        return [];
    }

    function extractTableData(payload) {
        if (Array.isArray(payload?.data?.data)) {
            return {
                rows: payload.data.data,
                total: Number(
                    payload.data.total
                    ?? payload.data.data.length
                ),
            };
        }

        if (Array.isArray(payload?.data)) {
            return {
                rows: payload.data,
                total: payload.data.length,
            };
        }

        return {
            rows: [],
            total: 0,
        };
    }

    renderMap(
        initialMapPorts,
        false
    );

    portForm.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            const params =
                new URLSearchParams();

            const query =
                portQuery.value.trim();

            const country =
                portCountry.value.trim();

            if (query) {
                params.set('q', query);
            }

            if (country) {
                params.set('country', country);
            }

            sg.loading(true);

            try {
                const mapUrl =
                    `{{ url('/api/ports/map') }}?${params.toString()}&limit=1000`;

                const tableUrl =
                    `{{ url('/api/ports') }}?${params.toString()}&per_page=30`;

                const [mapPayload, tablePayload] =
                    await Promise.all([
                        sg.fetchJson(mapUrl),
                        sg.fetchJson(tableUrl),
                    ]);

                const markers =
                    extractMapItems(mapPayload);

                const table =
                    extractTableData(tablePayload);

                renderMap(markers);
                renderRows(table.rows);

                filteredPortCount.textContent =
                    new Intl.NumberFormat('id-ID')
                        .format(table.total);

                serverPagination
                    ?.classList
                    .add('d-none');

                const targetUrl =
                    params.toString()
                        ? `{{ route('ports.index') }}?${params.toString()}`
                        : `{{ route('ports.index') }}`;

                history.replaceState(
                    {},
                    '',
                    targetUrl
                );

                sg.toast(
                    `${new Intl.NumberFormat('id-ID').format(markers.length)} lokasi pelabuhan ditemukan.`
                );
            } catch (error) {
                console.error(error);

                sg.toast(
                    error.message
                    || 'Data pelabuhan gagal dimuat.',
                    false
                );
            } finally {
                sg.loading(false);
            }
        }
    );

    window.addEventListener(
        'resize',
        function () {
            map.invalidateSize();
        }
    );
});
</script>
@endpush
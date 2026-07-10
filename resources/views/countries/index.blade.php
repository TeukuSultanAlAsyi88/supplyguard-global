@extends('layouts.app')
@section('title','Data Negara')
@section('heading','Data Negara')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h2 class="page-title">Negara Dunia</h2><p class="text-muted mb-0">Data negara dari REST Countries dengan pencarian AJAX.</p></div>
    <form method="POST" action="{{ route('countries.sync') }}">@csrf<button class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i> Sinkronkan Negara</button></form>
</div>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i> REST Countries v5 membutuhkan <code>REST_COUNTRIES_API_KEY</code>. Jika belum diisi, data awal seeder tetap dapat digunakan.</div>
<div class="card mb-4"><div class="card-body"><form id="countrySearchForm" class="row g-3">
    <div class="col-md-7"><input id="countryQuery" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, ISO2, atau ISO3..."></div>
    <div class="col-md-3"><select id="countryRegion" class="form-select" name="region"><option value="">Semua wilayah</option>@foreach($regions as $region)<option value="{{ $region }}" @selected(request('region')===$region)>{{ $region }}</option>@endforeach</select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari AJAX</button></div>
</form></div></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Negara</th><th>Kode</th><th>Wilayah</th><th>Mata Uang</th><th>Populasi</th><th>Risiko</th><th></th></tr></thead><tbody id="countryRows">
@forelse($countries as $c)<tr><td><div class="d-flex align-items-center gap-3"><img src="{{ $c->flag_url }}" class="flag" alt=""><div><strong>{{ $c->name }}</strong><div class="small text-muted">{{ $c->capital ?: '-' }}</div></div></div></td><td>{{ $c->code }} / {{ $c->cca3 }}</td><td>{{ $c->region }}</td><td>{{ $c->currency_code ?: '-' }}</td><td>{{ $c->population?number_format($c->population):'-' }}</td><td>@if($c->latestRisk)<span class="badge {{ $c->latestRisk->risk_level==='Rendah'?'badge-low':($c->latestRisk->risk_level==='Sedang'?'badge-medium':'badge-high') }}">{{ $c->latestRisk->risk_level }}</span>@else<span class="text-muted">Belum dihitung</span>@endif</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('countries.show',$c) }}">Detail</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-5 text-muted">Data negara belum tersedia.</td></tr>@endforelse
</tbody></table></div><div id="serverPagination" class="card-footer bg-white">{{ $countries->links() }}</div></div>
@endsection
@push('scripts')
<script>
const countryForm=document.getElementById('countrySearchForm');
countryForm.addEventListener('submit',async event=>{
    event.preventDefault();
    const params=new URLSearchParams(new FormData(countryForm));params.set('per_page','100');
    sg.loading(true);
    try{
        const payload=await sg.fetchJson(`{{ url('/api/countries') }}?${params}`);
        const rows=payload.data.data || [];
        document.getElementById('serverPagination').classList.add('d-none');
        document.getElementById('countryRows').innerHTML=rows.length?rows.map(c=>{
            const risk=c.latest_risk;
            return `<tr><td><div class="d-flex align-items-center gap-3"><img src="${sg.escape(c.flag_url)}" class="flag" alt=""><div><strong>${sg.escape(c.name)}</strong><div class="small text-muted">${sg.escape(c.capital||'-')}</div></div></div></td><td>${sg.escape(c.code||'-')} / ${sg.escape(c.cca3||'-')}</td><td>${sg.escape(c.region||'-')}</td><td>${sg.escape(c.currency_code||'-')}</td><td>${sg.number(c.population)}</td><td>${risk?`<span class="badge ${sg.riskClass(risk.risk_level)}">${sg.escape(risk.risk_level)}</span>`:'<span class="text-muted">Belum dihitung</span>'}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ url('/negara') }}/${c.id}">Detail</a></td></tr>`;
        }).join(''):'<tr><td colspan="7" class="text-center py-5 text-muted">Negara tidak ditemukan.</td></tr>';
        history.replaceState({},'',`{{ route('countries.index') }}?${params}`);
    }catch(error){sg.toast(error.message,false)}finally{sg.loading(false)}
});
</script>
@endpush

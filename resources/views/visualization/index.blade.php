@extends('layouts.app')
@section('title','Visualisasi Data')
@section('heading','Visualisasi Data')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><div><h2 class="page-title">Grafik Analitik {{ $country?->name }}</h2><p class="text-muted mb-0">Tren GDP dan inflasi 10 tahun, kurs 30 hari, serta riwayat skor risiko.</p></div><span class="badge text-bg-light border">Chart.js</span></div>
<div class="card mb-4"><div class="card-body"><form class="row g-3 align-items-end"><div class="col-lg-9"><label class="form-label">Negara</label><select class="form-select" name="country">@foreach($countries as $c)<option value="{{ $c->id }}" @selected($country?->id===$c->id)>{{ $c->name }}</option>@endforeach</select></div><div class="col-lg-3 d-grid"><button class="btn btn-primary">Tampilkan Tren</button></div></form></div></div>
@if(!$country)<div class="alert alert-warning">Data negara belum tersedia. Jalankan sinkronisasi negara terlebih dahulu.</div>@else
<div class="row g-4"><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>GDP 10 Tahun</h5><canvas id="gdpChart"></canvas><div class="small text-muted mt-2">Sumber: World Bank API</div></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>Inflasi 10 Tahun</h5><canvas id="inflationChart"></canvas><div class="small text-muted mt-2">Persentase tahunan.</div></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>Nilai Tukar USD/{{ $country->currency_code }}</h5><canvas id="currencyTrend"></canvas><div class="small text-muted mt-2">Riwayat harian dari Frankfurter dan nilai terbaru ExchangeRate-API.</div></div></div></div><div class="col-lg-6"><div class="card h-100"><div class="card-body"><h5>Skor Risiko</h5><canvas id="riskTrend"></canvas><div class="small text-muted mt-2">Semakin tinggi skor, semakin tinggi risiko rantai pasok.</div></div></div></div></div>
@endif
@endsection
@if($country)@push('scripts')
<script>
function emptyAware(id,labels,data,label,type='line',max=null){const canvas=document.getElementById(id);if(!data.length){canvas.insertAdjacentHTML('afterend','<div class="alert alert-light border mt-3 mb-0">Belum ada data tren. Jalankan sinkronisasi atau hitung risiko terlebih dahulu.</div>');}new Chart(canvas,{type,data:{labels,datasets:[{label,data,tension:.3,fill:type==='line'}]},options:{responsive:true,interaction:{mode:'index',intersect:false},scales:{y:{beginAtZero:false,...(max?{max}: {})}}}});}
emptyAware('gdpChart',@json($economics->pluck('year')),@json($economics->pluck('gdp')->map(fn($v)=>$v ? round($v/1e9,2) : null)),'GDP (Miliar USD)');
emptyAware('inflationChart',@json($economics->pluck('year')),@json($economics->pluck('inflation')),'Inflasi (%)','bar');
emptyAware('currencyTrend',@json($currencies->map(fn($x)=>$x->rate_date?->format('d/m') ?? $x->recorded_at?->format('d/m'))),@json($currencies->pluck('rate')),'USD/{{ $country->currency_code }}');
emptyAware('riskTrend',@json($risks->map(fn($x)=>$x->calculated_at?->format('d/m H:i'))),@json($risks->pluck('total_score')),'Skor Risiko','line',100);
</script>
@endpush @endif

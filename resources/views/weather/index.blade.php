@extends('layouts.app')
@section('title','Pemantauan Cuaca')
@section('heading','Pemantauan Cuaca Global')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><h2 class="page-title">Cuaca Global</h2><p class="text-muted mb-0">Pantau temperatur, hujan, angin kencang, dan risiko badai tanpa memuat ulang halaman.</p></div>
    <span class="badge text-bg-light border"><i class="bi bi-broadcast me-1"></i> Sumber: Open-Meteo</span>
</div>
<div class="card mb-4"><div class="card-body">
    <form id="weatherForm" class="row g-3 align-items-end">
        <div class="col-lg-9"><label class="form-label">Negara</label><select id="countrySelect" class="form-select" required><option value="">Pilih negara</option>@foreach($countries as $c)<option value="{{ $c->id }}" @selected($country?->id===$c->id)>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-lg-3 d-grid"><button class="btn btn-primary"><i class="bi bi-cloud-download me-1"></i> Perbarui Cuaca</button></div>
    </form>
</div></div>
<div class="row g-4 mb-4">
    @foreach([['temperature','Temperatur','°C','bi-thermometer-half'],['apparent_temperature','Terasa Seperti','°C','bi-person-arms-up'],['precipitation','Curah Hujan',' mm','bi-cloud-rain'],['precipitation_probability','Peluang Hujan','%','bi-droplet'],['wind_speed','Kecepatan Angin',' km/jam','bi-wind'],['wind_gust','Hembusan Angin',' km/jam','bi-tornado'],['humidity','Kelembapan','%','bi-moisture'],['storm_risk','Risiko Badai',' / 100','bi-exclamation-triangle']] as $card)
    <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><div class="text-muted small">{{ $card[1] }}</div><i class="bi {{ $card[3] }} text-primary"></i></div><div class="fs-3 fw-bold mt-2" id="metric-{{ $card[0] }}">{{ $weather && $weather->{$card[0]} !== null ? number_format($weather->{$card[0]},1).$card[2] : '-' }}</div></div></div></div>
    @endforeach
</div>
<div class="row g-4">
    <div class="col-xl-8"><div class="card"><div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between"><div><h5 class="mb-1">Peta Risiko Cuaca</h5><small class="text-muted">Lingkaran menunjukkan tingkat risiko badai dari negara yang telah disinkronkan.</small></div><span id="weatherCondition" class="badge text-bg-secondary align-self-start">{{ $weather?->condition ?? 'Belum tersedia' }}</span></div><div class="card-body pt-2"><div id="weatherMap" class="map"></div></div></div></div>
    <div class="col-xl-4"><div class="card h-100"><div class="card-body"><h5>Legenda Risiko</h5><div class="d-grid gap-3 mt-3"><div><span class="badge badge-low me-2">Rendah</span> 0–30</div><div><span class="badge badge-medium me-2">Sedang</span> 31–60</div><div><span class="badge badge-high me-2">Tinggi</span> 61–100</div></div><hr><dl class="row mb-0"><dt class="col-6">Negara aktif</dt><dd id="activeCountry" class="col-6 text-end">{{ $country?->name ?? '-' }}</dd><dt class="col-6">Pembaruan</dt><dd id="observedAt" class="col-6 text-end">{{ $weather?->observed_at?->format('d/m/Y H:i') ?? '-' }}</dd><dt class="col-6">Kondisi</dt><dd id="conditionText" class="col-6 text-end">{{ $weather?->condition ?? '-' }}</dd></dl><div class="alert alert-info mt-4 mb-0 small">Peta global akan semakin lengkap setelah command sinkronisasi cuaca dijalankan secara berkala.</div></div></div></div>
</div>
@endsection
@push('scripts')
<script>
const initialWeather = @json($weather);
const initialCountry = @json($country);
const overview = @json($mapWeather);
const map = L.map('weatherMap').setView([5, 110], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap contributors'}).addTo(map);
const globalLayer = L.layerGroup().addTo(map);
let selectedLayer = null;
function riskColor(score){return score <= 30 ? '#16a34a' : (score <= 60 ? '#d97706' : '#dc2626');}
function plotOverview(items){
    globalLayer.clearLayers();
    (items || []).forEach(w => {
        const c=w.country;if(!c || c.latitude===null || c.longitude===null)return;
        const score=Number(w.storm_risk||0);
        L.circleMarker([c.latitude,c.longitude],{radius:7+score/16,color:riskColor(score),fillColor:riskColor(score),fillOpacity:.68,weight:2})
          .bindPopup(`<strong>${sg.escape(c.name)}</strong><br>Kondisi: ${sg.escape(w.condition||'-')}<br>Hujan: ${sg.number(w.precipitation,1)} mm<br>Angin: ${sg.number(w.wind_speed,1)} km/jam<br>Risiko badai: ${sg.number(score,1)}/100`).addTo(globalLayer);
    });
}
function renderWeather(w,c){
    const suffix={temperature:'°C',apparent_temperature:'°C',precipitation:' mm',precipitation_probability:'%',wind_speed:' km/jam',wind_gust:' km/jam',humidity:'%',storm_risk:' / 100'};
    Object.keys(suffix).forEach(k=>document.getElementById(`metric-${k}`).textContent=w?.[k]===null||w?.[k]===undefined?'-':`${sg.number(w[k],1)}${suffix[k]}`);
    document.getElementById('activeCountry').textContent=c?.name||'-';document.getElementById('observedAt').textContent=w?.observed_at?new Date(w.observed_at).toLocaleString('id-ID'):'-';document.getElementById('conditionText').textContent=w?.condition||'-';document.getElementById('weatherCondition').textContent=w?.condition||'Tidak tersedia';
    if(selectedLayer)map.removeLayer(selectedLayer);
    if(c && c.latitude!==null && c.longitude!==null){selectedLayer=L.marker([c.latitude,c.longitude]).addTo(map).bindPopup(`<strong>${sg.escape(c.name)}</strong><br>${sg.escape(w?.condition||'-')}<br>Risiko: ${sg.number(w?.storm_risk,1)}/100`).openPopup();map.setView([c.latitude,c.longitude],5);}
}
plotOverview(overview);renderWeather(initialWeather,initialCountry);
document.getElementById('weatherForm').addEventListener('submit',async e=>{
    e.preventDefault();const id=document.getElementById('countrySelect').value;if(!id)return;
    sg.loading(true);try{const payload=await sg.fetchJson(`{{ url('/api/weather') }}/${id}?force=1`);const option=document.querySelector(`#countrySelect option[value="${id}"]`);const country={id:Number(id),name:option.textContent};const selected=@json($countries->mapWithKeys(fn($c)=>[$c->id=>['latitude'=>$c->latitude,'longitude'=>$c->longitude]]));Object.assign(country,selected[id]||{});renderWeather(payload.data,country);sg.toast('Data cuaca berhasil diperbarui.');history.replaceState({},'',`{{ route('weather.index') }}?country=${id}`);}catch(err){sg.toast(err.message,false);}finally{sg.loading(false);}
});
</script>
@endpush

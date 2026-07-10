@extends('layouts.app')
@section('title','Dokumentasi REST API')
@section('heading','Dokumentasi REST API')
@section('content')
@php
$groups=[
'Negara'=>[['GET','/api/countries','Daftar negara + filter/paginasi'],['GET','/api/countries/regions','Daftar wilayah'],['GET','/api/countries/{country}','Detail negara'],['GET','/api/countries/{country}/economics','Ekonomi terbaru'],['GET','/api/countries/{country}/economics/history','Riwayat ekonomi'],['GET','/api/countries/{country}/weather','Cuaca negara'],['GET','/api/countries/{country}/ports','Pelabuhan negara'],['GET','/api/countries/{country}/risks','Riwayat risiko']],
'Cuaca'=>[['GET','/api/weather','Peta cuaca global'],['GET','/api/weather/{country}','Cuaca terkini'],['GET','/api/weather/{country}/history','Riwayat cuaca']],
'Nilai Tukar'=>[['GET','/api/currency?base=USD&target=IDR','Kurs terbaru'],['GET','/api/currency/history?base=USD&target=IDR&days=30','Riwayat kurs'],['GET','/api/currency/convert?amount=100&base=USD&target=IDR','Konversi nilai'],['GET','/api/currency/pairs','Pasangan mata uang tersimpan']],
'Risiko'=>[['GET','/api/risk','Daftar skor risiko'],['GET','/api/risk/summary','Ringkasan tingkat risiko'],['GET','/api/risk/country/{country}','Risiko terbaru negara'],['POST','/api/risk/country/{country}/calculate','Hitung weighted risk'],['GET','/api/risk/{riskScore}/components','Rincian komponen dan bobot']],
'Pelabuhan'=>[['GET','/api/ports','Daftar pelabuhan'],['GET','/api/ports/map','Marker peta pelabuhan'],['GET','/api/ports/countries','Negara dalam dataset'],['GET','/api/ports/{port}','Detail pelabuhan']],
'Berita dan Sentimen'=>[['GET','/api/news?q=shipping','Daftar berita + sentimen'],['GET','/api/news/summary','Persentase sentimen'],['POST','/api/news/analyze','Analisis teks mandiri'],['GET','/api/news/{news}','Detail berita dan kata terdeteksi'],['GET','/api/sentiment/words','Kamus positif/negatif']],
'Analitik'=>[['GET','/api/comparison?a=1&b=2','Perbandingan dua negara'],['GET','/api/dashboard/summary','Statistik dasbor'],['GET','/api/dashboard/charts','Data grafik dasbor'],['GET','/api/integrations/status','Status integrasi API']],
'Artikel'=>[['GET','/api/articles','Daftar artikel analisis'],['GET','/api/articles/{article}','Detail artikel']],
];
@endphp
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><div><h2 class="page-title">35 Endpoint SupplyGuard</h2><p class="text-muted mb-0">Endpoint JSON untuk browser, JavaScript AJAX, atau Postman.</p></div><span class="badge text-bg-success fs-6">{{ collect($groups)->flatten(1)->count() }} endpoint</span></div>
<div class="alert alert-info"><strong>Base URL lokal:</strong> <code>{{ url('/api') }}</code>. Ganti parameter <code>{country}</code>, <code>{port}</code>, dan lainnya menggunakan ID database.</div>
@foreach($groups as $group=>$endpoints)<div class="card mb-4"><div class="card-header bg-white border-0 pt-4 px-4"><h5>{{ $group }} <span class="badge text-bg-light border">{{ count($endpoints) }}</span></h5></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th style="width:90px">Metode</th><th>Endpoint</th><th>Keterangan</th><th>Uji</th></tr></thead><tbody>@foreach($endpoints as $e)<tr><td><span class="badge {{ $e[0]==='GET'?'text-bg-success':'text-bg-primary' }}">{{ $e[0] }}</span></td><td><code>{{ $e[1] }}</code></td><td>{{ $e[2] }}</td><td>@if($e[0]==='GET' && !str_contains($e[1],'{'))<a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ url($e[1]) }}">Buka</a>@else<span class="text-muted small">Gunakan Postman / ganti ID</span>@endif</td></tr>@endforeach</tbody></table></div></div>@endforeach
@endsection

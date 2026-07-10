@extends('layouts.app')
@section('title','Daftar Pemantauan')
@section('heading','Daftar Pemantauan')
@section('content')
<div class="mb-4"><h2 class="page-title">Negara Favorit</h2><p class="text-muted mb-0">Simpan negara yang perlu dipantau rutin. Penambahan dan penghapusan berjalan tanpa reload.</p></div>
<div class="card mb-4"><div class="card-body"><form id="watchForm" class="row g-3 align-items-end"><div class="col-lg-9"><label class="form-label">Negara</label><select class="form-select" id="countrySelect" required><option value="">Pilih negara</option>@foreach($countries as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div><div class="col-lg-3 d-grid"><button class="btn btn-primary"><i class="bi bi-star me-1"></i> Tambahkan via AJAX</button></div></form></div></div>
<div id="watchGrid" class="row g-4"></div>
<div id="watchEmpty" class="alert alert-info {{ $items->isEmpty()?'':'d-none' }}">Belum ada negara dalam daftar pemantauan.</div>
@endsection
@push('scripts')
<script>
const initialItems=@json($items);
function card(item){const c=item.country||{},risk=c.latest_risk||c.latestRisk;return `<div class="col-md-6 col-xl-4" data-watch-id="${item.id}"><div class="card h-100"><div class="card-body"><div class="d-flex align-items-center gap-3">${c.flag_url?`<img src="${sg.escape(c.flag_url)}" class="flag" alt="Bendera">`:''}<div><h5 class="mb-0">${sg.escape(c.name)}</h5><small class="text-muted">${sg.escape(c.capital||'-')}</small></div></div><hr>${risk?`<div>Skor risiko: <strong>${sg.number(risk.total_score,1)}</strong></div><span class="badge mt-2 ${sg.riskClass(risk.risk_level)}">${sg.escape(risk.risk_level)}</span>`:'<p class="text-muted">Belum ada perhitungan risiko.</p>'}<div class="d-flex gap-2 mt-4"><a class="btn btn-sm btn-outline-primary" href="{{ url('/negara') }}/${c.id}">Lihat</a><button class="btn btn-sm btn-outline-danger remove-watch" data-id="${item.id}">Hapus</button></div></div></div></div>`;}
function render(items){document.getElementById('watchGrid').innerHTML=items.map(card).join('');document.getElementById('watchEmpty').classList.toggle('d-none',items.length>0);}
render(initialItems);
document.getElementById('watchForm').addEventListener('submit',async e=>{e.preventDefault();const id=document.getElementById('countrySelect').value;if(!id)return;sg.loading(true);try{const payload=await sg.fetchJson(`{{ url('/daftar-pemantauan') }}/${id}`,{method:'POST'});const old=document.querySelector(`[data-watch-id="${payload.data.id}"]`);if(!old)document.getElementById('watchGrid').insertAdjacentHTML('afterbegin',card(payload.data));document.getElementById('watchEmpty').classList.add('d-none');sg.toast(payload.message);}catch(err){sg.toast(err.message,false);}finally{sg.loading(false);}});
document.getElementById('watchGrid').addEventListener('click',async e=>{const btn=e.target.closest('.remove-watch');if(!btn||!confirm('Hapus negara dari daftar pemantauan?'))return;sg.loading(true);try{const payload=await sg.fetchJson(`{{ url('/daftar-pemantauan') }}/${btn.dataset.id}`,{method:'DELETE'});btn.closest('[data-watch-id]').remove();document.getElementById('watchEmpty').classList.toggle('d-none',document.querySelectorAll('[data-watch-id]').length>0);sg.toast(payload.message);}catch(err){sg.toast(err.message,false);}finally{sg.loading(false);}});
</script>
@endpush

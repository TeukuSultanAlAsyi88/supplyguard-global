<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama pelabuhan</label><input class="form-control" name="name" value="{{ old('name',$port?->name) }}" required></div>
    <div class="col-md-3"><label class="form-label">UN/LOCODE</label><input class="form-control" name="unlocode" value="{{ old('unlocode',$port?->unlocode) }}"></div>
    <div class="col-md-3"><label class="form-label">Nomor WPI</label><input class="form-control" name="wpi_number" value="{{ old('wpi_number',$port?->wpi_number) }}"></div>
    <div class="col-md-4"><label class="form-label">Kota</label><input class="form-control" name="city" value="{{ old('city',$port?->city) }}"></div>
    <div class="col-md-4"><label class="form-label">Negara</label><input class="form-control" name="country_name" value="{{ old('country_name',$port?->country_name) }}" required></div>
    <div class="col-md-4"><label class="form-label">Relasi negara (opsional)</label><select class="form-select" name="country_id"><option value="">Tidak dipilih</option>@foreach($countries as $countryOption)<option value="{{ $countryOption->id }}" @selected(old('country_id',$port?->country_id)==$countryOption->id)>{{ $countryOption->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Latitude</label><input class="form-control" name="latitude" type="number" step="any" value="{{ old('latitude',$port?->latitude) }}" required></div>
    <div class="col-md-3"><label class="form-label">Longitude</label><input class="form-control" name="longitude" type="number" step="any" value="{{ old('longitude',$port?->longitude) }}" required></div>
    <div class="col-md-3"><label class="form-label">Ukuran pelabuhan</label><input class="form-control" name="harbor_size" value="{{ old('harbor_size',$port?->harbor_size) }}"></div>
    <div class="col-md-3"><label class="form-label">Jenis harbor</label><input class="form-control" name="harbor_type" value="{{ old('harbor_type',$port?->harbor_type) }}"></div>
    <div class="col-md-4"><label class="form-label">Jenis fasilitas</label><input class="form-control" name="port_type" value="{{ old('port_type',$port?->port_type ?? 'Pelabuhan Laut') }}" required></div>
    <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option @selected(old('status',$port?->status)==='Aktif')>Aktif</option><option @selected(old('status',$port?->status)==='Tidak Aktif')>Tidak Aktif</option></select></div>
    <div class="col-md-4"><label class="form-label">Sumber data</label><input class="form-control" name="data_source" value="{{ old('data_source',$port?->data_source ?? 'Manual') }}"></div>
</div>

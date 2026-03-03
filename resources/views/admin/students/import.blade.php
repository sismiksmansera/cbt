@extends('layouts.admin')
@section('title', 'Import Siswa')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3>Import Siswa dari CSV</h3></div>
    <div class="card-body-padded">
        <div class="alert" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);color:#60a5fa;">
            <i class="fas fa-info-circle"></i>
            <div>Format CSV: <strong>NISN, Nama, Kelas, JK (L/P)</strong><br><small>Baris pertama dianggap header dan akan dilewati.</small></div>
        </div>
        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>File CSV</label>
                <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

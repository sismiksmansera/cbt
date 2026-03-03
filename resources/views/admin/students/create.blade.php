@extends('layouts.admin')
@section('title', 'Tambah Siswa')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3>Tambah Siswa Baru</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.students.store') }}">
            @csrf
            <div class="form-group">
                <label>NISN <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas" class="form-control" value="{{ old('kelas') }}" placeholder="cth: XII IPA 1">
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

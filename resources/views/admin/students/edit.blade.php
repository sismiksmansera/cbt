@extends('layouts.admin')
@section('title', 'Edit Siswa')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h3>Edit Siswa: {{ $student->nama }}</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.students.update', $student->id) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>NISN <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nisn" class="form-control" value="{{ $student->nisn }}" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nama" class="form-control" value="{{ $student->nama }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text" name="kelas" class="form-control" value="{{ $student->kelas }}">
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ $student->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $student->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Password Baru <small style="color:var(--text-secondary);">(Kosongkan jika tidak diubah)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $student->is_active ? 'checked' : '' }}> Aktif
                </label>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

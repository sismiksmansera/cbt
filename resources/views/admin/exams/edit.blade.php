@extends('layouts.admin')
@section('title', 'Edit Kategori')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header"><h3>Edit Kategori: {{ $exam->kategori }}</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.exams.update', $exam->id) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Kategori *</label>
                <input type="text" name="kategori" class="form-control" value="{{ $exam->kategori }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Mata Pelajaran *</label>
                    <select name="subject_id" class="form-control" required>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ $exam->subject_id == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Khusus Agama</label>
                <select name="agama" class="form-control">
                    <option value="">Semua Agama</option>
                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                        <option value="{{ $agm }}" {{ $exam->agama == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
                <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block;">Kosongkan jika soal berlaku untuk semua agama</small>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="2">{{ $exam->deskripsi }}</textarea>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>KKM</label>
                    <input type="number" name="passing_grade" class="form-control" value="{{ $exam->passing_grade }}" min="0" max="100">
                </div>
                <div class="form-group">
                    <label>Opsi</label>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                            <input type="checkbox" name="shuffle_questions" value="1" {{ $exam->shuffle_questions ? 'checked' : '' }}> Acak soal
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                            <input type="checkbox" name="shuffle_options" value="1" {{ $exam->shuffle_options ? 'checked' : '' }}> Acak opsi
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                            <input type="checkbox" name="show_result" value="1" {{ $exam->show_result ? 'checked' : '' }}> Tampilkan hasil
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                            <input type="checkbox" name="is_active" value="1" {{ $exam->is_active ? 'checked' : '' }}> Aktif
                        </label>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Buat Kategori Baru')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header"><h3>Buat Kategori Baru</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.exams.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Kategori <span style="color:var(--danger);">*</span></label>
                <input type="text" name="kategori" class="form-control" value="{{ old('kategori') }}" required>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Mata Pelajaran <span style="color:var(--danger);">*</span></label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Khusus Agama</label>
                <select name="agama" class="form-control">
                    <option value="">Semua Agama</option>
                    @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm)
                        <option value="{{ $agm }}" {{ old('agama') == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                    @endforeach
                </select>
                <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block;">Kosongkan jika soal berlaku untuk semua agama</small>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi') }}</textarea>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>KKM / Passing Grade</label>
                    <input type="number" name="passing_grade" class="form-control" value="{{ old('passing_grade', 70) }}" min="0" max="100">
                </div>
                <div class="form-group">
                    <label>Opsi</label>
                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                            <input type="checkbox" name="shuffle_questions" value="1"> Acak urutan soal
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                            <input type="checkbox" name="shuffle_options" value="1"> Acak opsi jawaban
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-primary);cursor:pointer;">
                            <input type="checkbox" name="show_result" value="1" checked> Tampilkan hasil ke siswa
                        </label>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Buat Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection

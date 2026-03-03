@extends('layouts.admin')
@section('title', 'Mata Pelajaran')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-book" style="color:var(--primary);margin-right:8px;"></i>Daftar Mata Pelajaran <span class="badge badge-info" style="margin-left:8px;">{{ $subjects->count() }}</span></h3>
        <div style="display:flex;gap:8px;">
            <form action="{{ route('admin.subjects.sync-simas') }}" method="POST" onsubmit="return confirm('Sinkronkan mata pelajaran dari SIMAS?')">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-sync"></i> Sync SIMAS</button>
            </form>
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').classList.add('active')"><i class="fas fa-plus"></i> Tambah</button>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Nama</th><th>Kode</th><th>Jumlah Ujian</th><th>Jumlah Soal</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($subjects as $s)
                <tr>
                    <td style="font-weight:600;">{{ $s->nama }}</td>
                    <td><code style="background:rgba(99,102,241,0.1);padding:3px 8px;border-radius:6px;color:var(--primary-light);">{{ $s->kode ?? '-' }}</code></td>
                    <td><span class="badge badge-info">{{ $s->exams_count }}</span></td>
                    <td><span class="badge badge-purple">{{ $s->questions_count }}</span></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <button class="btn btn-outline btn-icon btn-sm" onclick="editSubject({{ $s->id }}, '{{ $s->nama }}', '{{ $s->kode }}', '{{ $s->deskripsi }}')" title="Edit"><i class="fas fa-pen"></i></button>
                            <form action="{{ route('admin.subjects.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?')">@csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="fas fa-book"></i><p>Belum ada mata pelajaran</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Mata Pelajaran</h3><button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button></div>
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label>Nama *</label><input type="text" name="nama" class="form-control" required></div>
                <div class="form-group"><label>Kode</label><input type="text" name="kode" class="form-control" placeholder="cth: MTK"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header"><h3>Edit Mata Pelajaran</h3><button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button></div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group"><label>Nama *</label><input type="text" name="nama" id="editNama" class="form-control" required></div>
                <div class="form-group"><label>Kode</label><input type="text" name="kode" id="editKode" class="form-control"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editSubject(id, nama, kode, deskripsi) {
    document.getElementById('editForm').action = '/admin/subjects/' + id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editKode').value = kode;
    document.getElementById('editDeskripsi').value = deskripsi;
    document.getElementById('editModal').classList.add('active');
}
</script>
@endsection

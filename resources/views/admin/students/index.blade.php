@extends('layouts.admin')
@section('title', 'Manajemen Siswa')

@section('content')
<div class="stat-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-value">{{ $students->total() }}</div><div class="stat-label">Total Siswa</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div><div class="stat-value">{{ \App\Models\Student::where('is_active', true)->count() }}</div><div class="stat-label">Siswa Aktif</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-layer-group"></i></div>
        <div><div class="stat-value">{{ $kelasList->count() }}</div><div class="stat-label">Jumlah Kelas</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="search-bar">
            <form action="" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, NISN..." value="{{ request('search') }}">
                <select name="kelas" class="form-control" style="max-width:150px;" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.students.sync-simas') }}" class="btn btn-success btn-sm"><i class="fas fa-sync"></i> Sync SIMAS</a>
            <a href="{{ route('admin.students.import') }}" class="btn btn-outline btn-sm"><i class="fas fa-file-import"></i> Import</a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>JK</th>
                    <th>Agama</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $i => $s)
                <tr>
                    <td>{{ $students->firstItem() + $i }}</td>
                    <td><code style="background:rgba(99,102,241,0.1);padding:3px 8px;border-radius:6px;font-size:13px;color:var(--primary-light);">{{ $s->nisn }}</code></td>
                    <td style="font-weight:600;">{{ $s->nama }}</td>
                    <td>{{ $s->kelas ?? '-' }}</td>
                    <td>{{ $s->jenis_kelamin ?? '-' }}</td>
                    <td>{{ $s->agama ?? '-' }}</td>
                    <td><span class="badge {{ $s->is_active ? 'badge-success' : 'badge-danger' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('student.login', ['nisn' => $s->nisn]) }}" target="_blank" class="btn btn-sm btn-icon" style="background:var(--info);color:white;" title="Login sebagai siswa"><i class="fas fa-sign-in-alt"></i></a>
                            <a href="{{ route('admin.students.edit', $s->id) }}" class="btn btn-outline btn-icon btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.students.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="empty-state"><i class="fas fa-users"></i><p>Belum ada data siswa</p></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($students->hasPages())
        <div class="pagination">{{ $students->links('pagination::simple-bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Data Guru')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h3 style="font-size:18px;">Data Guru</h3>
        <p style="color:var(--text-secondary);font-size:13px;">{{ $teachers->count() }} guru terdaftar</p>
    </div>
    <form action="{{ route('admin.teachers.sync-simas') }}" method="POST">
        @csrf
        <button class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Sinkron dari SIMAS</button>
    </form>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@php
    $guruCount = $teachers->where('sumber', 'guru')->count();
    $bkCount = $teachers->where('sumber', 'guru_bk')->count();
@endphp
<div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-chalkboard-teacher"></i></div>
        <div><div class="stat-value">{{ $teachers->count() }}</div><div class="stat-label">Total Guru</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-tie"></i></div>
        <div><div class="stat-value">{{ $guruCount }}</div><div class="stat-label">Guru Mapel</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-hands-helping"></i></div>
        <div><div class="stat-value">{{ $bkCount }}</div><div class="stat-label">Guru BK</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-venus-mars"></i></div>
        <div><div class="stat-value">{{ $teachers->where('jenis_kelamin', 'L')->count() }}L / {{ $teachers->where('jenis_kelamin', 'P')->count() }}P</div><div class="stat-label">Gender</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Daftar Guru</h3></div>
    <div class="card-body">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>L/P</th>
                    <th>Jabatan</th>
                    <th>Mapel</th>
                    <th>Sumber</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:600;">{{ $t->nama }}</td>
                    <td style="font-size:13px;">{{ $t->nip ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $t->jenis_kelamin === 'L' ? 'badge-info' : 'badge-purple' }}" style="font-size:11px;">
                            {{ $t->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </td>
                    <td style="font-size:13px;">{{ $t->jabatan ?? '-' }}</td>
                    <td style="font-size:13px;">{{ $t->mapel_diampu ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $t->sumber === 'guru' ? 'badge-success' : 'badge-warning' }}" style="font-size:10px;">
                            {{ $t->sumber === 'guru' ? 'Guru Mapel' : 'Guru BK' }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('admin.teachers.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus data guru ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-secondary);">
                    <i class="fas fa-chalkboard-teacher" style="font-size:32px;opacity:0.3;display:block;margin-bottom:12px;"></i>
                    Belum ada data guru. Klik "Sinkron dari SIMAS" untuk mengambil data.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

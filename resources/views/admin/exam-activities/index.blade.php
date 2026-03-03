@extends('layouts.admin')
@section('title', 'Kegiatan Ujian')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:8px;"></i>Kegiatan Ujian</h3>
        <a href="{{ route('admin.exam-activities.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Kegiatan</a>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        <table>
            <thead><tr><th>Nama Kegiatan</th><th>Tanggal</th><th>Peserta</th><th>Pengawas</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($activities as $a)
                <tr>
                    <td style="font-weight:600;">{{ $a->nama_kegiatan }}</td>
                    <td style="font-size:13px;color:var(--text-secondary);">
                        {{ \Carbon\Carbon::parse($a->tanggal_pelaksanaan)->format('d M Y') }}
                        @if($a->tanggal_selesai)
                        <br><span style="font-size:11px;">s/d {{ \Carbon\Carbon::parse($a->tanggal_selesai)->format('d M Y') }}</span>
                        @endif
                    </td>
                    <td><span class="badge badge-info">{{ $a->peserta_ujian }}</span></td>
                    <td>
                        <span class="badge badge-purple" title="{{ $a->supervisors->pluck('nama_pengawas')->join(', ') }}">
                            <i class="fas fa-user-tie" style="font-size:10px;"></i> {{ $a->supervisors->count() }} pengawas
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.exam-activities.edit', $a->id) }}" class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            <a href="{{ route('admin.exam-sessions.index', ['activity_id' => $a->id]) }}" class="btn btn-primary btn-sm" title="Setting Ujian"><i class="fas fa-cog"></i></a>
                            <a href="{{ route('admin.exam-activities.print-cards', $a->id) }}" class="btn btn-sm" style="background:#7c3aed;color:#fff;" title="Cetak Kartu Peserta" target="_blank"><i class="fas fa-id-card"></i></a>
                            <form action="{{ route('admin.exam-activities.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini?')">@csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="fas fa-calendar-alt"></i><p>Belum ada kegiatan ujian</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

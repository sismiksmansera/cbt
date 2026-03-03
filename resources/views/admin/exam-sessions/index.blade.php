@extends('layouts.admin')
@section('title', $activity ? $activity->nama_kegiatan . ' - Setting Ujian' : 'Setting Ujian')

@section('content')
@if($activity)
<div style="margin-bottom:16px;">
    <a href="{{ route('admin.exam-activities.index') }}" style="color:var(--text-secondary);text-decoration:none;font-size:13px;">
        <i class="fas fa-arrow-left"></i> Kembali ke Kegiatan Ujian
    </a>
</div>
<div style="padding:14px 18px;border-radius:14px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
        <h3 style="font-size:16px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">{{ $activity->nama_kegiatan }}</h3>
        <span style="font-size:12px;color:var(--text-secondary);">
            <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($activity->tanggal_pelaksanaan)->format('d M Y') }} - {{ \Carbon\Carbon::parse($activity->tanggal_selesai)->format('d M Y') }}
            &nbsp;&bull;&nbsp;
            <i class="fas fa-users"></i> Peserta: {{ $activity->peserta_ujian }}
        </span>
    </div>
    <a href="{{ route('admin.exam-sessions.create', ['activity_id' => $activity->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Sesi</a>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-cog" style="color:var(--primary);margin-right:8px;"></i>Daftar Setting Ujian</h3>
        @if(!$activity)
        <a href="{{ route('admin.exam-sessions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Sesi</a>
        @endif
    </div>
    <div class="card-body">
        @if(session('success'))
        <div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        <table>
            <thead><tr><th>Sesi</th><th>Jumlah Soal</th><th>Token</th><th>Kelompok Tes</th><th>Peserta</th><th>Durasi</th><th>Jadwal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td style="font-weight:600;">{{ $s->nama_sesi }}</td>
                    <td><span class="badge badge-purple">{{ $s->categories->count() }} soal</span></td>
                    <td><code style="background:rgba(245,158,11,0.15);padding:4px 10px;border-radius:6px;font-size:14px;font-weight:700;color:var(--warning);letter-spacing:2px;">{{ $s->token }}</code></td>
                    <td><span class="badge badge-purple"><i class="fas fa-layer-group" style="font-size:10px;"></i> {{ $s->session_groups_count }} kelompok</span></td>
                    <td><span class="badge badge-info">{{ $s->students_count }} peserta</span></td>
                    <td><span class="badge badge-info"><i class="fas fa-clock" style="font-size:10px;"></i> {{ $s->durasi ?? '-' }} mnt</span></td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        {{ $s->waktu_mulai ? \Carbon\Carbon::parse($s->waktu_mulai)->format('d/m/Y H:i') : '-' }}<br>
                        {{ $s->waktu_selesai ? \Carbon\Carbon::parse($s->waktu_selesai)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>
                        @if($s->status === 'active')<span class="badge badge-success"><i class="fas fa-circle" style="font-size:8px;"></i> Aktif</span>
                        @elseif($s->status === 'pending')<span class="badge badge-warning">Menunggu</span>
                        @else<span class="badge badge-danger">Selesai</span>@endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                            <a href="{{ route('admin.exam-sessions.edit', $s->id) }}" class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;min-width:30px;text-align:center;" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            <a href="{{ route('admin.exam-sessions.monitor', $s->id) }}" class="btn btn-sm" style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;min-width:30px;text-align:center;" title="Monitor"><i class="fas fa-tv"></i></a>
                            <form action="{{ route('admin.exam-sessions.toggle-status', $s->id) }}" method="POST">@csrf
                                @if($s->status === 'pending')
                                    <button class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;min-width:30px;text-align:center;" title="Aktifkan"><i class="fas fa-play"></i></button>
                                @elseif($s->status === 'active')
                                    <button class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#b45309);color:#fff;min-width:30px;text-align:center;" onclick="return confirm('Akhiri sesi ini?')" title="Akhiri"><i class="fas fa-stop"></i></button>
                                @endif
                            </form>
                            @if($s->status === 'finished')
                            <form action="{{ route('admin.exam-sessions.restart', $s->id) }}" method="POST">@csrf
                                <button class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;min-width:30px;text-align:center;" onclick="return confirm('Mulai kembali sesi ini? Semua data ujian siswa akan direset!')" title="Mulai Kembali"><i class="fas fa-redo"></i></button>
                            </form>
                            @endif
                            <a href="{{ route('admin.exam-sessions.print-attendance', $s->id) }}" class="btn btn-sm" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;min-width:30px;text-align:center;" title="Cetak Daftar Hadir" target="_blank"><i class="fas fa-clipboard-list"></i></a>
                            <form action="{{ route('admin.exam-sessions.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi?')">@csrf @method('DELETE')
                                <button class="btn btn-sm" style="background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;min-width:30px;text-align:center;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="fas fa-cog"></i><p>Belum ada setting ujian{{ $activity ? ' untuk kegiatan ini' : '' }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Monitor Sesi')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h3 style="font-size:18px;">{{ $session->nama_sesi }}</h3>
        <p style="color:var(--text-secondary);font-size:13px;">{{ $session->categories->count() }} soal • Durasi: {{ $session->durasi }} mnt • Token: <strong style="color:var(--warning);letter-spacing:2px;">{{ $session->token }}</strong></p>
    </div>
    <div style="display:flex;gap:8px;">
        <form action="{{ route('admin.exam-sessions.toggle-status', $session->id) }}" method="POST">@csrf
            @if($session->status === 'pending')
                <button class="btn btn-success btn-sm"><i class="fas fa-play"></i> Aktifkan</button>
            @elseif($session->status === 'active')
                <button class="btn btn-warning btn-sm" onclick="return confirm('Akhiri sesi?')"><i class="fas fa-stop"></i> Akhiri</button>
            @endif
        </form>
        <span class="badge {{ $session->status === 'active' ? 'badge-success' : ($session->status === 'pending' ? 'badge-warning' : 'badge-danger') }}" style="font-size:13px;padding:8px 16px;">
            {{ ucfirst($session->status) }}
        </span>
    </div>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@php
    $total = $sessionStudents->count();
    $working = $sessionStudents->where('status', 'mengerjakan')->count();
    $done = $sessionStudents->where('status', 'selesai')->count();
    $waiting = $sessionStudents->where('status', 'belum_mulai')->count();
    $locked = $sessionStudents->where('is_locked', true)->count();
@endphp

<div class="stat-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-value">{{ $total }}</div><div class="stat-label">Total Peserta</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div><div class="stat-value">{{ $waiting }}</div><div class="stat-label">Belum Mulai</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-pencil-alt"></i></div>
        <div><div class="stat-value">{{ $working }}</div><div class="stat-label">Mengerjakan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div><div class="stat-value">{{ $done }}</div><div class="stat-label">Selesai</div></div>
    </div>
    @if($locked > 0)
    <div class="stat-card" style="border-color:rgba(239,68,68,0.3);">
        <div class="stat-icon" style="background:rgba(239,68,68,0.15);"><i class="fas fa-lock" style="color:var(--danger);"></i></div>
        <div><div class="stat-value" style="color:var(--danger);">{{ $locked }}</div><div class="stat-label">Terkunci</div></div>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header"><h3>Status Peserta</h3></div>
    <div class="card-body">
        <table>
            <thead><tr><th>Nama</th><th>NISN</th><th>Kelas</th><th>Status</th><th>Login</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($sessionStudents as $ss)
                <tr style="{{ $ss->is_locked ? 'background:rgba(239,68,68,0.05);' : '' }}">
                    <td style="font-weight:600;">
                        {{ $ss->student->nama ?? '-' }}
                        @if($ss->is_locked)
                            <span class="badge badge-danger" style="font-size:10px;margin-left:4px;"><i class="fas fa-lock"></i> TERKUNCI</span>
                        @endif
                    </td>
                    <td>{{ $ss->student->nisn ?? '-' }}</td>
                    <td>{{ $ss->student->kelas ?? '-' }}</td>
                    <td>
                        @if($ss->status === 'mengerjakan')<span class="badge badge-warning"><i class="fas fa-pencil-alt" style="font-size:10px;"></i> Mengerjakan</span>
                        @elseif($ss->status === 'selesai')<span class="badge badge-success"><i class="fas fa-check" style="font-size:10px;"></i> Selesai</span>
                        @else<span class="badge badge-info">Belum Mulai</span>@endif
                    </td>
                    <td>
                        <span class="badge {{ $ss->login_count > 1 ? 'badge-warning' : 'badge-info' }}" style="font-size:11px;">
                            <i class="fas fa-sign-in-alt"></i> {{ $ss->login_count ?? 0 }}x
                        </span>
                    </td>
                    <td style="font-size:13px;color:var(--text-secondary);">{{ $ss->waktu_mulai ? \Carbon\Carbon::parse($ss->waktu_mulai)->format('H:i:s') : '-' }}</td>
                    <td style="font-size:13px;color:var(--text-secondary);">{{ $ss->waktu_selesai ? \Carbon\Carbon::parse($ss->waktu_selesai)->format('H:i:s') : '-' }}</td>
                    <td>
                        <div style="display:inline-flex;gap:4px;flex-wrap:wrap;">
                        @if($ss->is_locked)
                            <form action="{{ route('admin.exam-sessions.unlock-student', [$session->id, $ss->student_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-success btn-sm" onclick="return confirm('Buka kunci siswa ini?')"><i class="fas fa-unlock"></i> Buka Kunci</button>
                            </form>
                        @endif
                        @if($ss->status === 'mengerjakan' || $ss->is_locked)
                            <form action="{{ route('admin.exam-sessions.force-submit', [$session->id, $ss->student_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-warning btn-sm" onclick="return confirm('Kumpulkan paksa ujian siswa ini? Jawaban yang sudah ada akan dinilai.')"><i class="fas fa-paper-plane"></i> Kumpulkan</button>
                            </form>
                        @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

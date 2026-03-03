@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
        <div><div class="stat-value">{{ $totalStudents }}</div><div class="stat-label">Total Siswa</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-file-alt"></i></div>
        <div><div class="stat-value">{{ $totalExams }}</div><div class="stat-label">Total Ujian</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
        <div><div class="stat-value">{{ $activeSessions }}</div><div class="stat-label">Sesi Aktif</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
        <div><div class="stat-value">{{ number_format($avgScore, 1) }}</div><div class="stat-label">Rata-rata Nilai</div></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>Ujian Terbaru</h3>
            <a href="{{ route('admin.exams.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Ujian</a>
        </div>
        <div class="card-body">
            @forelse($recentExams as $exam)
            <div style="padding:14px 20px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-weight:600;font-size:14px;">{{ $exam->kategori }}</div>
                    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">{{ $exam->subject->nama ?? '-' }} • {{ $exam->durasi }} menit</div>
                </div>
                <span class="badge {{ $exam->is_active ? 'badge-success' : 'badge-danger' }}">
                    {{ $exam->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @empty
            <div class="empty-state"><i class="fas fa-inbox"></i><p>Belum ada ujian</p></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="color:var(--success);margin-right:8px;"></i>Hasil Terbaru</h3>
            <a href="{{ route('admin.results.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body">
            @forelse($recentResults as $result)
            <div style="padding:12px 20px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-weight:600;font-size:13px;">{{ $result->student->nama ?? '-' }}</div>
                    <div style="font-size:11px;color:var(--text-secondary);">{{ $result->session->nama_sesi ?? '-' }}</div>
                </div>
                <span class="badge {{ $result->lulus ? 'badge-success' : 'badge-danger' }}">
                    {{ number_format($result->skor, 1) }}
                </span>
            </div>
            @empty
            <div class="empty-state"><i class="fas fa-clipboard-list"></i><p>Belum ada hasil ujian</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

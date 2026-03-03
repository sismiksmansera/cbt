@extends('layouts.admin')
@section('title', 'Detail Hasil: ' . ($session->nama_sesi ?? ''))

@section('content')
<div style="margin-bottom:20px;">
    <h3 style="font-size:18px;">{{ $session->nama_sesi ?? '-' }}</h3>
    <p style="color:var(--text-secondary);font-size:13px;">{{ $session->categories->count() }} soal • Durasi: {{ $session->durasi }} mnt</p>
</div>

<div class="card">
    <div class="card-header"><h3>Hasil Siswa</h3></div>
    <div class="card-body">
        <table>
            <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Kelas</th><th>Dijawab</th><th>Benar</th><th>Skor</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($results as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:600;">{{ $r->student->nama ?? '-' }}</td>
                    <td>{{ $r->student->nisn ?? '-' }}</td>
                    <td>{{ $r->student->kelas ?? '-' }}</td>
                    <td>{{ $r->dijawab }}/{{ $r->total_soal }}</td>
                    <td>{{ $r->benar }}</td>
                    <td>
                        <span style="font-size:18px;font-weight:800;color:{{ $r->lulus ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($r->skor, 1) }}</span>
                    </td>
                    <td><span class="badge {{ $r->lulus ? 'badge-success' : 'badge-danger' }}">{{ $r->lulus ? 'LULUS' : 'TIDAK LULUS' }}</span></td>
                    <td>
                        <a href="{{ route('admin.results.student-detail', [$session->id, $r->student_id]) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><p>Belum ada hasil</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Hasil Ujian')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar" style="color:var(--success);margin-right:8px;"></i>Hasil Ujian per Sesi</h3>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Sesi</th><th>Ujian</th><th>Jumlah Hasil</th><th>Rata-rata</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td style="font-weight:600;">{{ $s->nama_sesi ?? '-' }}</td>
                    <td>{{ $s->categories->count() }} soal</td>
                    <td><span class="badge badge-info">{{ $s->results_count }} siswa</span></td>
                    <td>
                        @php $avg = $s->results->avg('skor'); @endphp
                        <span class="badge {{ $avg >= 70 ? 'badge-success' : 'badge-danger' }}">{{ number_format($avg, 1) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.results.detail', $s->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="fas fa-chart-bar"></i><p>Belum ada hasil ujian</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

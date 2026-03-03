@extends('layouts.admin')
@section('title', 'Bank Soal')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px;"></i>Daftar Kategori Soal</h3>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Buat Kategori</a>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Kategori</th><th>Mata Pelajaran</th><th>Durasi</th><th>Soal</th><th>KKM</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($exams as $e)
                <tr>
                    <td style="font-weight:600;">{{ $e->kategori }}</td>
                    <td>{{ $e->subject->nama ?? '-' }}</td>
                    <td><span class="badge badge-info"><i class="fas fa-clock"></i> {{ $e->durasi ?? '-' }} mnt</span></td>
                    <td><span class="badge badge-purple">{{ $e->questions_count }} soal</span></td>
                    <td>{{ $e->passing_grade }}</td>
                    <td><span class="badge {{ $e->is_active ? 'badge-success' : 'badge-danger' }}">{{ $e->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.exams.questions', $e->id) }}" class="btn btn-outline btn-sm" title="Kelola Soal"><i class="fas fa-list"></i></a>
                            <a href="{{ route('admin.exams.edit', $e->id) }}" class="btn btn-outline btn-icon btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.exams.destroy', $e->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-file-alt"></i><p>Belum ada kategori soal</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

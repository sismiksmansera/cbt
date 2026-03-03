@extends('layouts.admin')
@section('title', 'Bank Soal')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="search-bar">
            <form action="" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
                <select name="exam_id" class="form-control" style="max-width:200px;" onchange="this.form.submit()">
                    <option value="">Semua Ujian</option>
                    @foreach($exams as $e)
                        <option value="{{ $e->id }}" {{ request('exam_id') == $e->id ? 'selected' : '' }}>{{ $e->kategori }}</option>
                    @endforeach
                </select>
                <select name="tipe" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="multiple_choice" {{ request('tipe') == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                    <option value="multiple_answer" {{ request('tipe') == 'multiple_answer' ? 'selected' : '' }}>Jawaban Ganda</option>
                    <option value="true_false" {{ request('tipe') == 'true_false' ? 'selected' : '' }}>Benar/Salah</option>
                    <option value="matching" {{ request('tipe') == 'matching' ? 'selected' : '' }}>Menjodohkan</option>
                    <option value="short_answer" {{ request('tipe') == 'short_answer' ? 'selected' : '' }}>Jawaban Singkat</option>
                    <option value="essay" {{ request('tipe') == 'essay' ? 'selected' : '' }}>Essay</option>
                </select>
            </form>
        </div>
        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah</a>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Pertanyaan</th><th>Ujian</th><th>Tipe</th><th>Bobot</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse($questions as $q)
                <tr>
                    <td style="max-width:300px;">{{ Str::limit($q->pertanyaan, 80) }}</td>
                    <td style="font-size:13px;">{{ $q->exam->kategori ?? '-' }}</td>
                    <td><span class="badge badge-info" style="font-size:11px;">{{ str_replace('_', ' ', ucfirst($q->tipe)) }}</span></td>
                    <td>{{ $q->bobot }}</td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.questions.edit', $q->id) }}" class="btn btn-outline btn-icon btn-sm"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
                                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="fas fa-question-circle"></i><p>Belum ada soal</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
        @if($questions->hasPages())
        <div class="pagination">{{ $questions->appends(request()->query())->links('pagination::simple-bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection

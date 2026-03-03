@extends('layouts.admin')
@section('title', 'Edit Soal')

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-header"><h3>Edit Soal</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.questions.update', $question->id) }}">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Ujian</label>
                    <select name="exam_id" class="form-control" disabled>
                        @foreach($exams as $e)
                            <option value="{{ $e->id }}" {{ $question->exam_id == $e->id ? 'selected' : '' }}>{{ $e->kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipe Soal</label>
                    <select name="tipe" id="tipe" class="form-control" onchange="toggleOptions()">
                        <option value="multiple_choice" {{ $question->tipe == 'multiple_choice' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="multiple_answer" {{ $question->tipe == 'multiple_answer' ? 'selected' : '' }}>Jawaban Ganda</option>
                        <option value="true_false" {{ $question->tipe == 'true_false' ? 'selected' : '' }}>Benar/Salah</option>
                        <option value="matching" {{ $question->tipe == 'matching' ? 'selected' : '' }}>Menjodohkan</option>
                        <option value="short_answer" {{ $question->tipe == 'short_answer' ? 'selected' : '' }}>Jawaban Singkat</option>
                        <option value="essay" {{ $question->tipe == 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Pertanyaan *</label>
                <textarea name="pertanyaan" class="form-control" rows="3" required>{{ $question->pertanyaan }}</textarea>
            </div>
            <div class="grid-2">
                <div class="form-group"><label>Bobot</label><input type="number" name="bobot" class="form-control" value="{{ $question->bobot }}" min="1"></div>
                <div class="form-group"><label>Pembahasan</label><textarea name="pembahasan" class="form-control" rows="2">{{ $question->pembahasan }}</textarea></div>
            </div>

            <!-- Options for MC/MA -->
            <div id="optionsSection" style="{{ in_array($question->tipe, ['multiple_choice','multiple_answer']) ? '' : 'display:none;' }}">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:12px;">Opsi Jawaban</label>
                <div id="optionsList">
                    @foreach($question->options as $i => $opt)
                    <div class="option-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
                        <input type="text" name="options[{{ $i }}][teks]" class="form-control" value="{{ $opt->teks_opsi }}" style="flex:1;">
                        <label style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--text-secondary);white-space:nowrap;">
                            <input type="checkbox" name="options[{{ $i }}][correct]" value="1" {{ $opt->is_correct ? 'checked' : '' }}> Benar
                        </label>
                        @if($i > 1)<button type="button" class="btn btn-danger btn-icon btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>@endif
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addOption()" style="margin-bottom:20px;"><i class="fas fa-plus"></i> Tambah Opsi</button>
            </div>

            <!-- T/F -->
            <div id="trueFalseSection" style="{{ $question->tipe == 'true_false' ? '' : 'display:none;' }}">
                @php $tfCorrect = $question->options->where('is_correct', true)->first(); @endphp
                <div style="display:flex;gap:16px;margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;padding:10px 20px;border-radius:10px;border:1px solid var(--border-color);cursor:pointer;color:var(--text-primary);">
                        <input type="radio" name="jawaban_benar" value="true" {{ $tfCorrect && $tfCorrect->teks_opsi == 'Benar' ? 'checked' : '' }}> Benar
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;padding:10px 20px;border-radius:10px;border:1px solid var(--border-color);cursor:pointer;color:var(--text-primary);">
                        <input type="radio" name="jawaban_benar" value="false" {{ $tfCorrect && $tfCorrect->teks_opsi == 'Salah' ? 'checked' : '' }}> Salah
                    </label>
                </div>
            </div>

            <!-- Matching -->
            <div id="matchingSection" style="{{ $question->tipe == 'matching' ? '' : 'display:none;' }}">
                <div id="pairsList">
                    @foreach($question->options as $i => $opt)
                    <div class="pair-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
                        <input type="text" name="pairs[{{ $i }}][left]" class="form-control" value="{{ $opt->teks_opsi }}" style="flex:1;">
                        <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
                        <input type="text" name="pairs[{{ $i }}][right]" class="form-control" value="{{ $opt->teks_pasangan }}" style="flex:1;">
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addPair()" style="margin-bottom:20px;"><i class="fas fa-plus"></i> Tambah</button>
            </div>

            <!-- Short Answer -->
            <div id="shortAnswerSection" style="{{ $question->tipe == 'short_answer' ? '' : 'display:none;' }}">
                <div class="form-group">
                    <label>Jawaban yang Benar</label>
                    <input type="text" name="jawaban_singkat" class="form-control" value="{{ $question->options->first()->teks_opsi ?? '' }}">
                </div>
            </div>

            <div id="essaySection" style="{{ $question->tipe == 'essay' ? '' : 'display:none;' }}">
                <div style="padding:14px;border-radius:10px;background:rgba(59,130,246,0.08);color:#60a5fa;font-size:13px;margin-bottom:20px;">
                    <i class="fas fa-info-circle"></i> Soal essay dinilai manual.
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.exams.questions', $question->exam_id) }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let optIdx = {{ $question->options->count() }}, pairIdx = {{ $question->options->count() }};
function toggleOptions() {
    const t = document.getElementById('tipe').value;
    document.getElementById('optionsSection').style.display = ['multiple_choice','multiple_answer'].includes(t) ? '' : 'none';
    document.getElementById('trueFalseSection').style.display = t === 'true_false' ? '' : 'none';
    document.getElementById('matchingSection').style.display = t === 'matching' ? '' : 'none';
    document.getElementById('shortAnswerSection').style.display = t === 'short_answer' ? '' : 'none';
    document.getElementById('essaySection').style.display = t === 'essay' ? '' : 'none';
}
function addOption() {
    const letter = String.fromCharCode(65 + optIdx);
    document.getElementById('optionsList').insertAdjacentHTML('beforeend', `<div class="option-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
        <input type="text" name="options[${optIdx}][teks]" class="form-control" placeholder="Opsi ${letter}" style="flex:1;">
        <label style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--text-secondary);white-space:nowrap;"><input type="checkbox" name="options[${optIdx}][correct]" value="1"> Benar</label>
        <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>`);
    optIdx++;
}
function addPair() {
    document.getElementById('pairsList').insertAdjacentHTML('beforeend', `<div class="pair-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
        <input type="text" name="pairs[${pairIdx}][left]" class="form-control" placeholder="Kiri" style="flex:1;">
        <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
        <input type="text" name="pairs[${pairIdx}][right]" class="form-control" placeholder="Kanan" style="flex:1;">
        <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>`);
    pairIdx++;
}
</script>
@endsection

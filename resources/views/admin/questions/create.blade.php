@extends('layouts.admin')
@section('title', 'Tambah Soal')

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-header"><h3>Tambah Soal Baru</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.questions.store') }}" id="questionForm">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $examId }}">

            <div class="grid-2">
                <div class="form-group">
                    <label>Kategori <span style="color:var(--danger);">*</span></label>
                    <select name="exam_id" class="form-control" required {{ $examId ? 'readonly' : '' }}>
                        <option value="">-- Pilih --</option>
                        @foreach($exams as $e)
                            <option value="{{ $e->id }}" {{ $examId == $e->id ? 'selected' : '' }}>{{ $e->kategori }} ({{ $e->subject->nama ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipe Soal <span style="color:var(--danger);">*</span></label>
                    <select name="tipe" id="tipe" class="form-control" required onchange="toggleOptions()">
                        <option value="multiple_choice">Pilihan Ganda</option>
                        <option value="multiple_answer">Jawaban Ganda</option>
                        <option value="true_false">Benar/Salah</option>
                        <option value="matching">Menjodohkan</option>
                        <option value="short_answer">Jawaban Singkat</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Pertanyaan <span style="color:var(--danger);">*</span></label>
                <div class="eq-toolbar" data-target="pertanyaan"></div>
                <textarea name="pertanyaan" id="pertanyaan" class="form-control eq-input" rows="3" required oninput="updatePreview('pertanyaan')">{{ old('pertanyaan') }}</textarea>
                <div class="eq-preview" id="pertanyaan_preview"></div>
            </div>

            <div class="grid-2">
                <div class="form-group"><label>Bobot</label><input type="number" name="bobot" class="form-control" value="1" min="1"></div>
                <div class="form-group"><label>Pembahasan</label><textarea name="pembahasan" class="form-control" rows="2"></textarea></div>
            </div>

            <!-- Multiple Choice / Multiple Answer Options -->
            <div id="optionsSection">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:12px;">Opsi Jawaban</label>
                <div id="optionsList">
                    @for($i = 0; $i < 4; $i++)
                    <div class="option-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:start;">
                        <div style="flex:1;">
                            <div class="eq-toolbar" data-target="opt_{{ $i }}"></div>
                            <input type="text" name="options[{{ $i }}][teks]" id="opt_{{ $i }}" class="form-control eq-input" placeholder="Opsi {{ chr(65 + $i) }}" oninput="updatePreview('opt_{{ $i }}')">
                            <div class="eq-preview eq-preview-sm" id="opt_{{ $i }}_preview"></div>
                        </div>
                        <label style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--text-secondary);white-space:nowrap;margin-top:8px;">
                            <input type="checkbox" name="options[{{ $i }}][correct]" value="1"> Benar
                        </label>
                    </div>
                    @endfor
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addOption()" style="margin-bottom:20px;"><i class="fas fa-plus"></i> Tambah Opsi</button>
            </div>

            <!-- True/False -->
            <div id="trueFalseSection" style="display:none;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:12px;">Jawaban Benar</label>
                <div style="display:flex;gap:16px;margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;padding:10px 20px;border-radius:10px;border:1px solid var(--border-color);color:var(--text-primary);">
                        <input type="radio" name="jawaban_benar" value="true"> Benar
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;padding:10px 20px;border-radius:10px;border:1px solid var(--border-color);color:var(--text-primary);">
                        <input type="radio" name="jawaban_benar" value="false"> Salah
                    </label>
                </div>
            </div>

            <!-- Matching -->
            <div id="matchingSection" style="display:none;">
                <label style="display:block;font-size:13px;font-weight:600;color:var(--text-secondary);margin-bottom:12px;">Pasangan</label>
                <div id="pairsList">
                    <div class="pair-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
                        <input type="text" name="pairs[0][left]" class="form-control" placeholder="Soal / Kiri" style="flex:1;">
                        <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
                        <input type="text" name="pairs[0][right]" class="form-control" placeholder="Jawaban / Kanan" style="flex:1;">
                    </div>
                    <div class="pair-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
                        <input type="text" name="pairs[1][left]" class="form-control" placeholder="Soal / Kiri" style="flex:1;">
                        <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
                        <input type="text" name="pairs[1][right]" class="form-control" placeholder="Jawaban / Kanan" style="flex:1;">
                    </div>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="addPair()" style="margin-bottom:20px;"><i class="fas fa-plus"></i> Tambah Pasangan</button>
            </div>

            <!-- Short Answer -->
            <div id="shortAnswerSection" style="display:none;">
                <div class="form-group">
                    <label>Jawaban yang Benar</label>
                    <input type="text" name="jawaban_singkat" class="form-control" placeholder="Masukkan jawaban yang benar">
                </div>
            </div>

            <!-- Essay -->
            <div id="essaySection" style="display:none;">
                <div style="padding:14px;border-radius:10px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.15);color:#60a5fa;font-size:13px;margin-bottom:20px;">
                    <i class="fas fa-info-circle"></i> Soal essay akan dinilai secara manual oleh admin/guru.
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ $examId ? route('admin.exams.questions', $examId) : route('admin.questions.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Soal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .eq-toolbar {
        display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 6px;
    }
    .eq-toolbar .eq-btn {
        padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border-color);
        background: rgba(15,23,42,0.4); color: var(--text-secondary); font-size: 12px;
        cursor: pointer; transition: all 0.15s; font-family: 'Inter', serif;
    }
    .eq-toolbar .eq-btn:hover {
        border-color: var(--primary); color: var(--primary-light); background: rgba(99,102,241,0.1);
    }
    .eq-preview {
        min-height: 24px; padding: 10px 14px; margin-top: 6px;
        border-radius: 8px; background: rgba(15,23,42,0.4); border: 1px solid var(--border-color);
        font-size: 15px; color: var(--text-primary); display: none;
    }
    .eq-preview-sm { padding: 6px 10px; font-size: 13px; min-height: 20px; }
    .eq-preview.has-math { display: block; }
</style>
@endsection

@section('scripts')
<script>
// Equation toolbar buttons
const eqButtons = [
    { label: 'x²', tex: '^{2}' },
    { label: 'xⁿ', tex: '^{n}' },
    { label: '√', tex: '\\sqrt{}' },
    { label: '∛', tex: '\\sqrt[3]{}' },
    { label: 'frac', tex: '\\frac{}{}' },
    { label: 'π', tex: '\\pi' },
    { label: '∑', tex: '\\sum_{i=1}^{n}' },
    { label: '∫', tex: '\\int_{a}^{b}' },
    { label: '∞', tex: '\\infty' },
    { label: '±', tex: '\\pm' },
    { label: '×', tex: '\\times' },
    { label: '÷', tex: '\\div' },
    { label: '≤', tex: '\\leq' },
    { label: '≥', tex: '\\geq' },
    { label: '≠', tex: '\\neq' },
    { label: 'α', tex: '\\alpha' },
    { label: 'β', tex: '\\beta' },
    { label: 'θ', tex: '\\theta' },
    { label: 'log', tex: '\\log' },
    { label: 'sin', tex: '\\sin' },
    { label: 'cos', tex: '\\cos' },
    { label: 'lim', tex: '\\lim_{x \\to}' },
];

// Build toolbars
document.querySelectorAll('.eq-toolbar').forEach(toolbar => {
    const targetId = toolbar.dataset.target;
    eqButtons.forEach(btn => {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = 'eq-btn';
        el.textContent = btn.label;
        el.title = btn.tex;
        el.onclick = () => insertEquation(targetId, btn.tex);
        toolbar.appendChild(el);
    });
});

function insertEquation(targetId, tex) {
    const el = document.getElementById(targetId);
    if (!el) return;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const val = el.value;

    // If no existing \( \), wrap in delimiters
    const insertion = '\\(' + tex + '\\)';
    el.value = val.substring(0, start) + insertion + val.substring(end);
    el.focus();

    // Place cursor inside the equation
    const cursorPos = start + insertion.length - 2; // before \)
    el.setSelectionRange(cursorPos, cursorPos);

    updatePreview(targetId);
}

function updatePreview(targetId) {
    const el = document.getElementById(targetId);
    const preview = document.getElementById(targetId + '_preview');
    if (!el || !preview) return;

    const val = el.value;
    if (val.includes('\\(') || val.includes('$$') || /\$[^$]+\$/.test(val)) {
        preview.classList.add('has-math');
        preview.innerHTML = val.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        renderAllMath(preview);
    } else {
        preview.classList.remove('has-math');
    }
}

let optIdx = 4, pairIdx = 2;

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
    const html = `<div class="option-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:start;">
        <div style="flex:1;">
            <div class="eq-toolbar" data-target="opt_${optIdx}"></div>
            <input type="text" name="options[${optIdx}][teks]" id="opt_${optIdx}" class="form-control eq-input" placeholder="Opsi ${letter}" oninput="updatePreview('opt_${optIdx}')">
            <div class="eq-preview eq-preview-sm" id="opt_${optIdx}_preview"></div>
        </div>
        <label style="display:flex;align-items:center;gap:4px;font-size:12px;color:var(--text-secondary);white-space:nowrap;margin-top:8px;">
            <input type="checkbox" name="options[${optIdx}][correct]" value="1"> Benar
        </label>
        <button type="button" class="btn btn-danger btn-icon btn-sm" style="margin-top:4px;" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>`;
    document.getElementById('optionsList').insertAdjacentHTML('beforeend', html);

    // Build toolbar for new option
    const newToolbar = document.querySelector(`[data-target="opt_${optIdx}"]`);
    if (newToolbar) {
        eqButtons.forEach(btn => {
            const el = document.createElement('button');
            el.type = 'button';
            el.className = 'eq-btn';
            el.textContent = btn.label;
            el.title = btn.tex;
            el.onclick = () => insertEquation('opt_' + optIdx, btn.tex);
            newToolbar.appendChild(el);
        });
    }
    optIdx++;
}

function addPair() {
    const html = `<div class="pair-row" style="display:flex;gap:10px;margin-bottom:10px;align-items:center;">
        <input type="text" name="pairs[${pairIdx}][left]" class="form-control" placeholder="Soal / Kiri" style="flex:1;">
        <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
        <input type="text" name="pairs[${pairIdx}][right]" class="form-control" placeholder="Jawaban / Kanan" style="flex:1;">
        <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>`;
    document.getElementById('pairsList').insertAdjacentHTML('beforeend', html);
    pairIdx++;
}
</script>
@endsection

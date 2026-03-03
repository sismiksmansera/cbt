@extends('layouts.admin')
@section('title', 'Buat Setting Ujian')

@section('content')
<div class="card" style="max-width:800px;">
    <div class="card-header">
        <h3><i class="fas fa-cog" style="color:var(--primary);margin-right:8px;"></i>Buat Setting Ujian Baru</h3>
    </div>
    <div class="card-body-padded">
        @if($activity)
        <div style="padding:12px 16px;border-radius:12px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);margin-bottom:20px;font-size:13px;color:#60a5fa;">
            <i class="fas fa-link"></i> Kegiatan: <strong>{{ $activity->nama_kegiatan }}</strong>
            &nbsp;|&nbsp; Peserta: <strong>{{ $activity->peserta_ujian }}</strong>
            &nbsp;|&nbsp; {{ \Carbon\Carbon::parse($activity->tanggal_pelaksanaan)->format('d M Y') }} - {{ \Carbon\Carbon::parse($activity->tanggal_selesai)->format('d M Y') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.exam-sessions.store') }}">
            @csrf
            @if($activity)
            <input type="hidden" name="exam_activity_id" value="{{ $activity->id }}">
            @endif

            <div class="form-group">
                <label>Nama Sesi <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nama_sesi" class="form-control" placeholder="cth: Sesi 1 - Kelas XII" value="{{ $activity ? $activity->nama_kegiatan : '' }}" required>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;justify-content:space-between;">
                    <span>Kelompok Soal <span style="color:var(--danger);">*</span></span>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openQgModal()" style="border-color:rgba(139,92,246,0.4);color:#a78bfa;">
                        <i class="fas fa-plus"></i> Tambah Kelompok Soal
                    </button>
                </label>
                <div id="questionGroupContainer" style="display:flex;flex-direction:column;gap:12px;margin-top:10px;"></div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Durasi (menit) <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="durasi" class="form-control" value="60" min="1" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Waktu Mulai <span style="color:var(--danger);">*</span></label>
                    <input type="datetime-local" name="waktu_mulai" class="form-control" required
                        @if($activity) min="{{ $activity->tanggal_pelaksanaan }}T00:00" max="{{ $activity->tanggal_selesai }}T23:59" @endif>
                </div>
                <div class="form-group">
                    <label>Waktu Selesai <span style="color:var(--danger);">*</span></label>
                    <input type="datetime-local" name="waktu_selesai" class="form-control" required
                        @if($activity) min="{{ $activity->tanggal_pelaksanaan }}T00:00" max="{{ $activity->tanggal_selesai }}T23:59" @endif>
                </div>
            </div>

            @if($activity && $activity->peserta_ujian === 'Guru')
            {{-- GURU as participants --}}
            <div class="form-group">
                <label>Pilih Peserta (Guru)</label>
                <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
                    <div style="padding:10px 12px;border-bottom:1px solid var(--border-color);background:rgba(255,255,255,0.02);">
                        <input type="text" id="searchStudent" placeholder="Cari guru..." oninput="filterStudents()" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:'Inter';">
                    </div>
                    <div style="padding:8px 12px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:var(--text-primary);">
                            <input type="checkbox" id="selectAll" onchange="toggleAll()"> Pilih Semua
                        </label>
                        <span id="studentCount" style="font-size:11px;color:var(--text-secondary);"></span>
                    </div>
                    <div style="max-height:300px;overflow-y:auto;padding:4px 12px;">
                        @foreach($teachers as $t)
                        <label class="student-row" data-search="{{ strtolower($t->nama . ' ' . $t->nip) }}" style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;cursor:pointer;color:var(--text-primary);">
                            <input type="checkbox" name="student_ids[]" value="{{ $t->id }}" class="student-cb">
                            {{ $t->nama }} <span style="color:var(--text-secondary);font-size:11px;">({{ $t->nip ?? '-' }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            @elseif($activity && $activity->kelompok_tes_mode !== 'tanpa' && $activity->groups->count() > 0)
            {{-- TEST GROUP SELECTION via modal --}}
            <div class="form-group">
                <label style="display:flex;align-items:center;justify-content:space-between;">
                    <span>Kelompok Tes</span>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openTestGroupModal()" style="border-color:rgba(139,92,246,0.4);color:#a78bfa;">
                        <i class="fas fa-layer-group"></i> Pilih Kelompok Tes
                    </button>
                </label>
                <div id="testGroupTags" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;"></div>
                <div id="testGroupInputs"></div>
                <div id="testGroupSummary" style="margin-top:8px;font-size:12px;color:var(--text-secondary);"></div>
            </div>

            @else
            {{-- TANPA KELOMPOK: individual student selection --}}
            <div class="form-group">
                <label>Pilih Peserta</label>
                <div style="margin-bottom:12px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                        <input type="checkbox" id="selectByKelas" name="select_by_kelas" value="1" onchange="toggleStudentSelect()"> Pilih berdasarkan kelas
                    </label>
                </div>

                <div id="kelasSelect" style="display:none;margin-bottom:16px;">
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach($kelasList as $k)
                        <label style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;border:1px solid var(--border-color);cursor:pointer;font-size:13px;color:var(--text-primary);">
                            <input type="checkbox" name="kelas_selected[]" value="{{ $k }}"> {{ $k }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div id="studentSelect">
                    <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
                        <div style="padding:10px 12px;border-bottom:1px solid var(--border-color);background:rgba(255,255,255,0.02);">
                            <input type="text" id="searchStudent" placeholder="Cari nama siswa, NISN, atau kelas..." oninput="filterStudents()" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:'Inter';">
                        </div>
                        <div style="padding:8px 12px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:var(--text-primary);">
                                <input type="checkbox" id="selectAll" onchange="toggleAll()"> Pilih Semua
                            </label>
                            <span id="studentCount" style="font-size:11px;color:var(--text-secondary);"></span>
                        </div>
                        <div style="max-height:300px;overflow-y:auto;padding:4px 12px;">
                            @foreach($students as $st)
                            <label class="student-row" data-search="{{ strtolower($st->nama . ' ' . $st->nisn . ' ' . ($st->kelas ?? '')) }}" style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;cursor:pointer;color:var(--text-primary);">
                                <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" class="student-cb">
                                {{ $st->nama }} <span style="color:var(--text-secondary);font-size:11px;">({{ $st->nisn }} - {{ $st->kelas ?? '-' }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.exam-sessions.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Buat Sesi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
const exams = @json($exams ?? []);
const availableRombels = @json($kelasList ?? []);
const rombelCounts = @json($rombelCounts ?? []);
let qgCount = 0;
let qgData = {}; // stores each group's data

function buildCategoryOptions(selectedId) {
    let options = '<option value="">-- Pilih Kategori --</option>';
    exams.forEach(e => {
        const subjectName = e.subject ? e.subject.nama : '';
        const qCount = e.questions_count !== undefined ? e.questions_count : '?';
        const sel = (selectedId && e.id == selectedId) ? 'selected' : '';
        options += `<option value="${e.id}" data-count="${qCount}" ${sel}>${e.kategori} (${subjectName}) — ${qCount} soal</option>`;
    });
    return options;
}

let modalCatCount = 0;

function addModalCategory(containerId, g, preset) {
    modalCatCount++;
    const c = modalCatCount;
    const container = document.getElementById(containerId);
    const row = document.createElement('div');
    row.id = `mCat-${c}`;
    row.style.cssText = 'padding:10px 12px;border-radius:10px;border:1px solid var(--border-color);background:rgba(255,255,255,0.02);';

    const options = buildCategoryOptions(preset ? preset.examId : null);
    const mode = preset ? preset.mode : 'semua';
    const jumlah = preset ? preset.jumlah : '';

    row.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <select class="form-control modal-cat-select" data-cat-id="${c}" style="flex:1;font-size:12px;padding:6px 10px;">
                ${options}
            </select>
            <button type="button" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:13px;padding:4px;" onclick="document.getElementById('mCat-${c}').remove()"><i class="fas fa-trash-alt"></i></button>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;color:var(--text-primary);padding:4px 10px;border-radius:6px;border:1px solid ${mode==='semua'?'rgba(16,185,129,0.3)':'var(--border-color)'};background:${mode==='semua'?'rgba(16,185,129,0.08)':''};">
                <input type="radio" name="mMode-${c}" value="semua" ${mode==='semua'?'checked':''} onchange="toggleModalMode(${c})"> Semua
            </label>
            <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;color:var(--text-primary);padding:4px 10px;border-radius:6px;border:1px solid ${mode==='sebagian'?'rgba(16,185,129,0.3)':'var(--border-color)'};background:${mode==='sebagian'?'rgba(16,185,129,0.08)':''};">
                <input type="radio" name="mMode-${c}" value="sebagian" ${mode==='sebagian'?'checked':''} onchange="toggleModalMode(${c})"> Sebagian
            </label>
            <div id="mPartial-${c}" style="display:${mode==='sebagian'?'flex':'none'};align-items:center;gap:5px;">
                <input type="number" id="mJumlah-${c}" min="1" placeholder="Jumlah" value="${jumlah}" style="width:70px;padding:4px 8px;border-radius:6px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:11px;font-family:'Inter';">
                <span style="font-size:10px;color:var(--text-secondary);">soal</span>
            </div>
        </div>
    `;
    container.appendChild(row);
}

function toggleModalMode(c) {
    const mode = document.querySelector(`input[name="mMode-${c}"]:checked`)?.value;
    const partial = document.getElementById(`mPartial-${c}`);
    if (partial) partial.style.display = mode === 'sebagian' ? 'flex' : 'none';
    document.querySelectorAll(`input[name="mMode-${c}"]`).forEach(r => {
        const lbl = r.closest('label');
        if (r.checked) { lbl.style.background = 'rgba(16,185,129,0.08)'; lbl.style.borderColor = 'rgba(16,185,129,0.3)'; }
        else { lbl.style.background = ''; lbl.style.borderColor = 'var(--border-color)'; }
    });
}

function openQgModal(editG) {
    let existing = document.getElementById('qgModal');
    if (existing) existing.remove();
    modalCatCount = 0;

    const isEdit = editG && qgData[editG];
    const data = isEdit ? qgData[editG] : null;

    const modalEl = document.createElement('div');
    modalEl.id = 'qgModal';
    modalEl.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px);';
    modalEl.onclick = function(e) { if (e.target === this) this.remove(); };

    let rombelHtml = '';
    if (availableRombels.length > 0) {
        const selRombels = data ? data.rombels : [];
        // Collect rombels used by OTHER question groups
        let usedRombels = [];
        for (const gid in qgData) {
            if (isEdit && parseInt(gid) === editG) continue;
            usedRombels = usedRombels.concat(qgData[gid].rombels || []);
        }
        const filteredRombels = availableRombels.filter(r => !usedRombels.includes(r) || selRombels.includes(r));
        if (filteredRombels.length > 0) {
            rombelHtml = `
                <div style="margin-top:14px;">
                    <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;"><i class="fas fa-school" style="margin-right:4px;"></i> Rombel (opsional)</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        ${filteredRombels.map(r => `<label style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:6px;border:1px solid ${selRombels.includes(r)?'rgba(139,92,246,0.4)':'var(--border-color)'};font-size:11px;cursor:pointer;color:var(--text-primary);background:${selRombels.includes(r)?'rgba(139,92,246,0.08)':''};"><input type="checkbox" class="modal-rombel-cb" value="${r}" ${selRombels.includes(r)?'checked':''} onchange="this.closest('label').style.background=this.checked?'rgba(139,92,246,0.08)':'';this.closest('label').style.borderColor=this.checked?'rgba(139,92,246,0.4)':'var(--border-color)';"> ${r}</label>`).join('')}
                    </div>
                </div>`;
        } else {
            rombelHtml = `<div style="margin-top:14px;padding:10px;border-radius:8px;background:rgba(139,92,246,0.06);font-size:12px;color:#a78bfa;"><i class="fas fa-info-circle"></i> Semua rombel sudah dipilih di kelompok soal lain.</div>`;
        }
    }

    modalEl.innerHTML = `
        <div style="background:var(--bg-card);border-radius:16px;width:550px;max-width:95vw;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.3);border:1px solid var(--border-color);">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);"><i class="fas fa-layer-group" style="color:#8b5cf6;margin-right:8px;"></i>${isEdit ? 'Edit' : 'Tambah'} Kelompok Soal</h3>
                <button onclick="document.getElementById('qgModal').remove()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:16px;"><i class="fas fa-times"></i></button>
            </div>
            <div style="padding:16px 20px;overflow-y:auto;flex:1;">
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:6px;display:block;">Nama Kelompok Soal</label>
                    <input type="text" id="qgModalName" class="form-control" placeholder="cth: Tipe A" value="${data ? data.name : ''}" style="font-size:13px;">
                </div>
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);"><i class="fas fa-list-alt" style="margin-right:4px;"></i> Kategori Soal</span>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addModalCategory('qgModalCats',0)" style="font-size:11px;padding:3px 8px;"><i class="fas fa-plus"></i> Kategori</button>
                    </div>
                    <div id="qgModalCats" style="display:flex;flex-direction:column;gap:8px;"></div>
                </div>
                ${rombelHtml}
            </div>
            <div style="padding:12px 20px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('qgModal').remove()">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveQgModal(${isEdit ? editG : 0})" style="font-size:12px;"><i class="fas fa-check"></i> Simpan</button>
            </div>
        </div>
    `;
    document.body.appendChild(modalEl);

    // Pre-fill categories
    if (data && data.categories.length > 0) {
        data.categories.forEach(cat => addModalCategory('qgModalCats', 0, cat));
    } else {
        addModalCategory('qgModalCats', 0);
    }
}

function saveQgModal(editG) {
    const name = document.getElementById('qgModalName').value.trim();
    if (!name) { alert('Nama kelompok soal wajib diisi.'); return; }

    // Collect categories
    const cats = [];
    document.querySelectorAll('#qgModalCats .modal-cat-select').forEach(sel => {
        if (sel.value) {
            const catId = sel.dataset.catId;
            const mode = document.querySelector(`input[name="mMode-${catId}"]:checked`)?.value || 'semua';
            const jumlah = document.getElementById(`mJumlah-${catId}`)?.value || '';
            const opt = sel.options[sel.selectedIndex];
            cats.push({ examId: sel.value, mode: mode, jumlah: jumlah, label: opt.textContent.trim() });
        }
    });
    if (cats.length === 0) { alert('Tambahkan minimal satu kategori soal.'); return; }

    // Collect rombels
    const rombels = [];
    document.querySelectorAll('.modal-rombel-cb:checked').forEach(cb => rombels.push(cb.value));

    // Determine group index
    const g = editG > 0 ? editG : ++qgCount;

    // Store data
    qgData[g] = { name, categories: cats, rombels };

    // Render summary card
    renderQgCard(g);

    document.getElementById('qgModal').remove();
}

function renderQgCard(g) {
    const data = qgData[g];
    if (!data) return;

    let existingCard = document.getElementById('qg' + g);
    if (existingCard) existingCard.remove();

    const container = document.getElementById('questionGroupContainer');
    const div = document.createElement('div');
    div.id = 'qg' + g;
    div.className = 'qg-card';
    div.style.cssText = 'border:1px solid rgba(139,92,246,0.25);border-radius:12px;overflow:hidden;background:rgba(139,92,246,0.03);';

    const catSummary = data.categories.map(c => {
        const modeTag = c.mode === 'sebagian' ? ` (${c.jumlah} soal)` : '';
        return `<span style="padding:3px 8px;border-radius:5px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);font-size:11px;color:#60a5fa;">${c.label}${modeTag}</span>`;
    }).join('');

    const rombelTags = data.rombels.length > 0 ? data.rombels.map(r =>
        `<span style="padding:2px 6px;border-radius:4px;background:rgba(139,92,246,0.1);font-size:10px;color:#a78bfa;">${r}</span>`
    ).join('') : '';

    // Hidden inputs
    let hiddenInputs = `<input type="hidden" name="qg[${g}][name]" value="${data.name}">`;
    data.categories.forEach((cat, i) => {
        const idx = i + 1;
        hiddenInputs += `<input type="hidden" name="qg[${g}][categories][${idx}]" value="${cat.examId}">`;
        hiddenInputs += `<input type="hidden" name="qg[${g}][display_mode][${idx}]" value="${cat.mode}">`;
        if (cat.mode === 'sebagian' && cat.jumlah) {
            hiddenInputs += `<input type="hidden" name="qg[${g}][jumlah_soal][${idx}]" value="${cat.jumlah}">`;
        }
    });
    data.rombels.forEach(r => {
        hiddenInputs += `<input type="hidden" name="qg[${g}][rombels][]" value="${r}">`;
    });

    div.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;">
            <div style="display:flex;align-items:center;gap:10px;flex:1;">
                <span style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:13px;flex-shrink:0;">${g}</span>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:600;color:var(--text-primary);">${data.name}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">${catSummary}</div>
                    ${rombelTags ? `<div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">${rombelTags}</div>` : ''}
                </div>
            </div>
            <div style="display:flex;gap:4px;">
                <button type="button" class="btn btn-info btn-icon btn-sm" onclick="openQgModal(${g})" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="removeQuestionGroup(${g})" title="Hapus"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        ${hiddenInputs}
    `;
    container.appendChild(div);
}

function removeQuestionGroup(g) {
    document.getElementById('qg' + g)?.remove();
    delete qgData[g];
}

// No auto-init — groups are added via modal

// ===== PARTICIPANT SELECTION HELPERS =====
function toggleStudentSelect() {
    const byKelas = document.getElementById('selectByKelas')?.checked;
    const kelasEl = document.getElementById('kelasSelect');
    const studentEl = document.getElementById('studentSelect');
    if (kelasEl) kelasEl.style.display = byKelas ? '' : 'none';
    if (studentEl) studentEl.style.display = byKelas ? 'none' : '';
}
function toggleAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.student-cb').forEach(cb => {
        if (cb.closest('.student-row').style.display !== 'none') cb.checked = checked;
    });
}
function filterStudents() {
    const el = document.getElementById('searchStudent');
    if (!el) return;
    const q = el.value.toLowerCase();
    let visible = 0;
    document.querySelectorAll('.student-row').forEach(row => {
        const match = row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const countEl = document.getElementById('studentCount');
    if (countEl) countEl.textContent = visible + ' peserta';
}

@if($activity && $activity->groups->count() > 0 && $activity->kelompok_tes_mode !== 'tanpa')
@php
    $groupsData = $activity->groups->map(function($g) {
        return ['id' => $g->id, 'nama' => $g->nama_kelompok, 'count' => $g->students->count()];
    });
@endphp
const activityGroups = @json($groupsData);
let selectedTestGroups = [];

function openTestGroupModal() {
    let existing = document.getElementById('testGroupModal');
    if (existing) existing.remove();

    const modalEl = document.createElement('div');
    modalEl.id = 'testGroupModal';
    modalEl.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px);';
    modalEl.onclick = function(e) { if (e.target === this) this.remove(); };

    let listHtml = '';
    activityGroups.forEach(g => {
        const isChecked = selectedTestGroups.includes(g.id);
        listHtml += `<label style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;border:1px solid ${isChecked ? 'rgba(139,92,246,0.5)' : 'var(--border-color)'};cursor:pointer;color:var(--text-primary);transition:all 0.15s;background:${isChecked ? 'rgba(139,92,246,0.08)' : ''};" onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'" onmouseout="if(!this.querySelector('input').checked)this.style.borderColor='var(--border-color)'">
            <input type="checkbox" value="${g.id}" ${isChecked ? 'checked' : ''} onchange="this.closest('label').style.background=this.checked?'rgba(139,92,246,0.08)':'';this.closest('label').style.borderColor=this.checked?'rgba(139,92,246,0.5)':'var(--border-color)';">
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;">${g.nama}</div>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">${g.count} siswa</div>
            </div>
            <i class="fas fa-users" style="color:var(--text-secondary);font-size:14px;"></i>
        </label>`;
    });

    modalEl.innerHTML = `
        <div style="background:var(--bg-card);border-radius:16px;width:450px;max-width:95vw;max-height:70vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,0.3);border:1px solid var(--border-color);">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);"><i class="fas fa-layer-group" style="color:#8b5cf6;margin-right:8px;"></i>Pilih Kelompok Tes</h3>
                <button onclick="document.getElementById('testGroupModal').remove()" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:16px;"><i class="fas fa-times"></i></button>
            </div>
            <div style="padding:16px 20px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                ${listHtml}
            </div>
            <div style="padding:12px 20px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;">
                <button type="button" class="btn btn-primary btn-sm" onclick="saveTestGroupSelection()" style="font-size:12px;"><i class="fas fa-check"></i> Simpan</button>
            </div>
        </div>
    `;
    document.body.appendChild(modalEl);
}

function saveTestGroupSelection() {
    const modal = document.getElementById('testGroupModal');
    selectedTestGroups = [];
    modal.querySelectorAll('input[type=checkbox]:checked').forEach(cb => selectedTestGroups.push(parseInt(cb.value)));
    renderTestGroupTags();
    modal.remove();
}

function removeTestGroup(id) {
    selectedTestGroups = selectedTestGroups.filter(g => g !== id);
    renderTestGroupTags();
}

function renderTestGroupTags() {
    const tagsEl = document.getElementById('testGroupTags');
    const inputsEl = document.getElementById('testGroupInputs');
    const summaryEl = document.getElementById('testGroupSummary');
    if (!tagsEl) return;

    if (selectedTestGroups.length > 0) {
        let totalStudents = 0;
        tagsEl.innerHTML = selectedTestGroups.map(id => {
            const g = activityGroups.find(x => x.id === id);
            if (g) totalStudents += g.count;
            return g ? `<span style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.25);font-size:12px;color:#a78bfa;font-weight:500;">
                <i class="fas fa-layer-group" style="font-size:10px;"></i> ${g.nama} <span style="opacity:0.7;">(${g.count})</span>
                <button type="button" onclick="removeTestGroup(${id})" style="background:none;border:none;color:#a78bfa;cursor:pointer;font-size:10px;padding:0;line-height:1;margin-left:2px;"><i class="fas fa-times"></i></button>
            </span>` : '';
        }).join('');
        inputsEl.innerHTML = selectedTestGroups.map(id => `<input type="hidden" name="group_ids[]" value="${id}">`).join('');
        if (summaryEl) summaryEl.textContent = `${selectedTestGroups.length} kelompok terpilih — Total ${totalStudents} siswa`;
    } else {
        tagsEl.innerHTML = '<span style="font-size:12px;color:var(--text-secondary);font-style:italic;">Belum ada kelompok tes dipilih</span>';
        inputsEl.innerHTML = '';
        if (summaryEl) summaryEl.textContent = '';
    }

    // Color question group blocks
    document.querySelectorAll('.qg-card').forEach(card => {
        if (selectedTestGroups.length > 0) {
            card.style.borderColor = 'rgba(16,185,129,0.3)';
            card.style.background = 'rgba(16,185,129,0.02)';
        } else {
            card.style.borderColor = 'var(--border-color)';
            card.style.background = '';
        }
    });
}
renderTestGroupTags();
@endif

if (document.getElementById('searchStudent')) filterStudents();
</script>
@endsection


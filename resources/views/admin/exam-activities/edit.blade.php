@extends('layouts.admin')
@section('title', 'Edit Kegiatan Ujian')

@section('content')
<style>
    .collapse-section { border:1px solid var(--border-color); border-radius:12px; overflow:hidden; margin-bottom:12px; }
    .collapse-header {
        display:flex; align-items:center; justify-content:space-between; padding:14px 16px;
        cursor:pointer; background:rgba(255,255,255,0.02); user-select:none; transition: background 0.2s;
    }
    .collapse-header:hover { background:rgba(255,255,255,0.04); }
    .collapse-header h5 { font-size:13px; font-weight:700; display:flex; align-items:center; gap:8px; color:var(--text-primary); }
    .collapse-header .arrow { transition: transform 0.3s; color:var(--text-secondary); font-size:12px; }
    .collapse-header.open .arrow { transform: rotate(180deg); }
    .collapse-body { padding:16px; border-top:1px solid var(--border-color); display:none; }
    .collapse-body.show { display:block; }
</style>

<div class="card" style="max-width:800px;">
    <div class="card-header"><h3><i class="fas fa-edit" style="color:var(--primary);margin-right:8px;"></i>Edit Kegiatan Ujian</h3></div>
    <div class="card-body-padded">
        <form method="POST" action="{{ route('admin.exam-activities.update', $activity->id) }}">
            @csrf @method('PUT')

            <div style="padding:16px;border-radius:14px;border:1px solid var(--border-color);background:rgba(59,130,246,0.04);margin-bottom:24px;">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:16px;color:var(--text-primary);"><i class="fas fa-id-card" style="color:var(--primary);margin-right:6px;"></i>Identitas Ujian</h4>

                <div class="form-group">
                    <label>Nama Kegiatan Ujian <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nama_kegiatan" class="form-control" value="{{ $activity->nama_kegiatan }}" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Tanggal Mulai <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="tanggal_pelaksanaan" class="form-control" value="{{ $activity->tanggal_pelaksanaan }}" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Akhir <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $activity->tanggal_selesai }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Peserta Ujian <span style="color:var(--danger);">*</span></label>
                    <select name="peserta_ujian" class="form-control" required>
                        <option value="Siswa" {{ $activity->peserta_ujian === 'Siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="Guru" {{ $activity->peserta_ujian === 'Guru' ? 'selected' : '' }}>Guru</option>
                        <option value="Lainnya" {{ $activity->peserta_ujian === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div style="padding:16px;border-radius:14px;border:1px solid var(--border-color);background:rgba(16,185,129,0.04);margin-bottom:24px;">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:16px;color:var(--text-primary);"><i class="fas fa-user-tie" style="color:var(--success);margin-right:6px;"></i>Pengawas Ujian</h4>

                <!-- Guru Collapse -->
                <div class="collapse-section">
                    <div class="collapse-header open" onclick="toggleCollapse(this)">
                        <h5><i class="fas fa-chalkboard-teacher" style="color:var(--primary);"></i> Guru</h5>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>
                    <div class="collapse-body show">
                        <div style="margin-bottom:10px;">
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:var(--text-primary);">
                                <input type="checkbox" id="selectAllTeachers" onchange="toggleAllTeachers()"> Pilih Semua Guru
                            </label>
                        </div>
                        <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
                            <div style="padding:10px 12px;border-bottom:1px solid var(--border-color);background:rgba(255,255,255,0.02);">
                                <input type="text" id="searchTeacher" placeholder="Cari guru..." oninput="filterTeachers()" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:13px;font-family:'Inter';">
                            </div>
                            <div style="max-height:200px;overflow-y:auto;padding:4px 12px;">
                                @foreach($teachers as $t)
                                <label class="teacher-row" data-search="{{ strtolower($t->nama . ' ' . $t->nip) }}" style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;cursor:pointer;color:var(--text-primary);">
                                    <input type="checkbox" name="teacher_ids[]" value="{{ $t->id }}" class="teacher-cb" {{ in_array($t->id, $selectedTeacherIds) ? 'checked' : '' }}>
                                    {{ $t->nama }} <span style="color:var(--text-secondary);font-size:11px;">({{ $t->nip ?? '-' }})</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengawas Lain Collapse -->
                <div class="collapse-section">
                    <div class="collapse-header {{ $externalSupervisors->count() > 0 ? 'open' : '' }}" onclick="toggleCollapse(this)">
                        <h5><i class="fas fa-user-plus" style="color:var(--warning);"></i> Pengawas Lain</h5>
                        <i class="fas fa-chevron-down arrow"></i>
                    </div>
                    <div class="collapse-body {{ $externalSupervisors->count() > 0 ? 'show' : '' }}">
                        <div id="externalRows" style="display:flex;flex-direction:column;gap:10px;"></div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="addExternalRow()" style="margin-top:10px;">
                            <i class="fas fa-plus"></i> Tambah Pengawas Lain
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kelompok Tes Section (only for Siswa) --}}
            <div id="kelompokTesSection" style="padding:16px;border-radius:14px;border:1px solid var(--border-color);background:rgba(139,92,246,0.04);margin-bottom:24px;{{ $activity->peserta_ujian !== 'Siswa' ? 'display:none;' : '' }}">
                <h4 style="font-size:14px;font-weight:700;margin-bottom:16px;color:var(--text-primary);"><i class="fas fa-layer-group" style="color:#8b5cf6;margin-right:6px;"></i>Setting Kelompok Tes</h4>

                <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);padding:10px 14px;border-radius:10px;border:1px solid var(--border-color);{{ $activity->kelompok_tes_mode === 'tanpa' ? 'background:rgba(139,92,246,0.1);border-color:rgba(139,92,246,0.3);' : '' }}">
                        <input type="radio" name="kelompok_tes_mode" value="tanpa" {{ $activity->kelompok_tes_mode === 'tanpa' ? 'checked' : '' }} onchange="switchGroupMode(this.value)">
                        <div><strong>Tanpa Kelompok Tes</strong><br><span style="font-size:11px;color:var(--text-secondary);">Peserta dipilih langsung saat membuat sesi ujian</span></div>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);padding:10px 14px;border-radius:10px;border:1px solid var(--border-color);{{ $activity->kelompok_tes_mode === 'rombel' ? 'background:rgba(139,92,246,0.1);border-color:rgba(139,92,246,0.3);' : '' }}">
                        <input type="radio" name="kelompok_tes_mode" value="rombel" {{ $activity->kelompok_tes_mode === 'rombel' ? 'checked' : '' }} onchange="switchGroupMode(this.value)">
                        <div><strong>Sesuai Rombel Siswa</strong><br><span style="font-size:11px;color:var(--text-secondary);">Kelompok dibuat otomatis berdasarkan kelas/rombel siswa</span></div>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);padding:10px 14px;border-radius:10px;border:1px solid var(--border-color);{{ $activity->kelompok_tes_mode === 'custom' ? 'background:rgba(139,92,246,0.1);border-color:rgba(139,92,246,0.3);' : '' }}">
                        <input type="radio" name="kelompok_tes_mode" value="custom" {{ $activity->kelompok_tes_mode === 'custom' ? 'checked' : '' }} onchange="switchGroupMode(this.value)">
                        <div><strong>Buat Kelompok Tes Baru</strong><br><span style="font-size:11px;color:var(--text-secondary);">Buat kelompok kustom dan pilih siswa per kelompok</span></div>
                    </label>
                </div>

                {{-- Rombel Preview --}}
                <div id="rombelPreview" style="{{ $activity->kelompok_tes_mode === 'rombel' ? '' : 'display:none;' }}padding:12px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid var(--border-color);">
                    <p style="font-size:12px;color:var(--text-secondary);margin-bottom:8px;"><i class="fas fa-info-circle"></i> Kelompok akan otomatis dibuat berdasarkan rombel:</p>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @php $kelasList = $students->pluck('kelas')->unique()->sort()->values(); @endphp
                        @foreach($kelasList as $k)
                        <span style="padding:4px 12px;border-radius:8px;background:rgba(139,92,246,0.15);color:#a78bfa;font-size:12px;font-weight:600;">{{ $k }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Groups --}}
                <div id="customGroups" style="{{ $activity->kelompok_tes_mode === 'custom' ? '' : 'display:none;' }}">
                    <div id="groupContainer" style="display:flex;flex-direction:column;gap:14px;"></div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addGroup()" style="margin-top:12px;">
                        <i class="fas fa-plus"></i> Tambah Kelompok
                    </button>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.exam-activities.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleCollapse(header) {
    header.classList.toggle('open');
    const body = header.nextElementSibling;
    body.classList.toggle('show');
}

const existingExternals = @json($externalSupervisors);
let extCount = 0;

function addExternalRow(nama = '', nip = '', jk = 'L', instansi = '') {
    extCount++;
    const container = document.getElementById('externalRows');
    const row = document.createElement('div');
    row.id = 'extRow' + extCount;
    row.style.cssText = 'padding:14px;border-radius:12px;border:1px solid var(--border-color);background:rgba(255,255,255,0.02);';
    const num = extCount;
    row.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <span style="font-size:12px;font-weight:700;color:var(--text-secondary);">Pengawas Eksternal #${num}</span>
            <button type="button" class="btn btn-danger btn-icon btn-sm" onclick="document.getElementById('extRow${num}').remove()" title="Hapus"><i class="fas fa-times"></i></button>
        </div>
        <div class="grid-2" style="gap:10px;">
            <div class="form-group" style="margin-bottom:8px;">
                <label style="font-size:11px;">Nama Pengawas *</label>
                <input type="text" name="ext_nama[]" class="form-control" value="${nama}" placeholder="Nama lengkap" required>
            </div>
            <div class="form-group" style="margin-bottom:8px;">
                <label style="font-size:11px;">NIP (Username)</label>
                <input type="text" name="ext_nip[]" class="form-control" value="${nip}" placeholder="NIP">
            </div>
        </div>
        <div class="grid-2" style="gap:10px;">
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:11px;">Jenis Kelamin</label>
                <select name="ext_jk[]" class="form-control">
                    <option value="L" ${jk === 'L' ? 'selected' : ''}>Laki-laki</option>
                    <option value="P" ${jk === 'P' ? 'selected' : ''}>Perempuan</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:11px;">Utusan / Asal Instansi</label>
                <input type="text" name="ext_instansi[]" class="form-control" value="${instansi}" placeholder="Nama instansi">
            </div>
        </div>
    `;
    container.appendChild(row);
}

// Load existing external supervisors
existingExternals.forEach(e => addExternalRow(e.nama_pengawas, e.nip || '', e.jenis_kelamin || 'L', e.asal_instansi || ''));

function toggleAllTeachers() {
    const checked = document.getElementById('selectAllTeachers').checked;
    document.querySelectorAll('.teacher-cb').forEach(cb => {
        if (cb.closest('.teacher-row').style.display !== 'none') cb.checked = checked;
    });
}
function filterTeachers() {
    const q = document.getElementById('searchTeacher').value.toLowerCase();
    document.querySelectorAll('.teacher-row').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
}

// ===== KELOMPOK TES =====
const allStudents = @json($students);
const existingGroups = @json($activity->groups->map(fn($g) => ['nama' => $g->nama_kelompok, 'student_ids' => $g->students->pluck('id')]));
let groupCount = 0;

// Extract unique kelas list
const kelasOptions = [...new Set(allStudents.map(s => s.kelas).filter(Boolean))].sort();

function switchGroupMode(mode) {
    document.getElementById('rombelPreview').style.display = mode === 'rombel' ? '' : 'none';
    document.getElementById('customGroups').style.display = mode === 'custom' ? '' : 'none';
    document.querySelectorAll('input[name="kelompok_tes_mode"]').forEach(r => {
        r.closest('label').style.background = r.checked ? 'rgba(139,92,246,0.1)' : '';
        r.closest('label').style.borderColor = r.checked ? 'rgba(139,92,246,0.3)' : 'var(--border-color)';
    });
}

function addGroup(nama = '', selectedIds = []) {
    groupCount++;
    const n = groupCount;
    const container = document.getElementById('groupContainer');
    const div = document.createElement('div');
    div.id = 'grp' + n;
    div.style.cssText = 'border:1px solid var(--border-color);border-radius:12px;overflow:hidden;';

    let kelasOpts = '<option value="">Semua Rombel</option>';
    kelasOptions.forEach(k => { kelasOpts += `<option value="${k}">${k}</option>`; });

    let studentHtml = '';
    allStudents.forEach(s => {
        const checked = selectedIds.includes(s.id) ? 'checked' : '';
        const search = (s.nama + ' ' + s.nisn + ' ' + (s.kelas || '')).toLowerCase();
        studentHtml += `<label class="gs-row" data-search="${search}" data-kelas="${s.kelas || ''}" data-sid="${s.id}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;font-size:12px;cursor:pointer;color:var(--text-primary);border-bottom:1px solid rgba(255,255,255,0.04);width:100%;">
            <input type="checkbox" name="group_students[${n}][]" value="${s.id}" ${checked} class="gs-cb-${n}" onchange="onGroupStudentChange()" style="flex-shrink:0;">
            <span style="flex:1;">${s.nama}</span> <span style="color:var(--text-secondary);font-size:10px;flex-shrink:0;">${s.kelas || '-'}</span>
        </label>`;
    });

    div.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:rgba(255,255,255,0.02);cursor:pointer;" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'':'none'">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="width:28px;height:28px;border-radius:8px;background:rgba(139,92,246,0.15);color:#a78bfa;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">${n}</span>
                <input type="text" name="group_names[${n}]" value="${nama}" placeholder="Nama Kelompok Tes" onclick="event.stopPropagation()" required style="border:none;background:transparent;color:var(--text-primary);font-size:13px;font-weight:600;font-family:'Inter';outline:none;width:200px;">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="gs-count-${n}" style="font-size:11px;color:var(--text-secondary);">0 siswa</span>
                <button type="button" onclick="event.stopPropagation();document.getElementById('grp${n}').remove();refreshAllGroupVisibility();" class="btn btn-danger btn-icon btn-sm"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div style="padding:10px 14px;border-top:1px solid var(--border-color);${selectedIds.length > 0 ? '' : 'display:none;'}">
            <div style="display:flex;gap:8px;margin-bottom:8px;">
                <input type="text" placeholder="Cari siswa..." oninput="applyGroupFilter(${n})" class="gs-search-${n}" style="flex:1;padding:6px 10px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:12px;font-family:'Inter';">
                <select class="gs-kelas-${n}" onchange="applyGroupFilter(${n})" style="padding:6px 10px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:12px;font-family:'Inter';min-width:120px;">${kelasOpts}</select>
            </div>
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--text-primary);margin-bottom:6px;">
                <input type="checkbox" onchange="toggleAllGroupStudents(${n}, this.checked)"> Pilih Semua (yang terlihat)
            </label>
            <div style="max-height:200px;overflow-y:auto;display:flex;flex-direction:column;" class="gs-list-${n}">${studentHtml}</div>
        </div>
    `;
    container.appendChild(div);
    updateGroupCount(n);
    refreshAllGroupVisibility();
}

// Get all student IDs checked across ALL groups except the given group
function getCheckedIdsExcept(exceptGroupNum) {
    const ids = new Set();
    document.querySelectorAll('[id^="grp"]').forEach(grpEl => {
        const gn = grpEl.id.replace('grp', '');
        if (gn == exceptGroupNum) return;
        grpEl.querySelectorAll('.gs-cb-' + gn + ':checked').forEach(cb => {
            ids.add(cb.value);
        });
    });
    return ids;
}

// Apply search + kelas filter + cross-group exclusion for a specific group
function applyGroupFilter(n) {
    const searchEl = document.querySelector('.gs-search-' + n);
    const kelasEl = document.querySelector('.gs-kelas-' + n);
    const q = searchEl ? searchEl.value.toLowerCase() : '';
    const kelas = kelasEl ? kelasEl.value : '';
    const takenIds = getCheckedIdsExcept(n);

    document.querySelectorAll('#grp' + n + ' .gs-row').forEach(row => {
        const sid = row.dataset.sid;
        const isTaken = takenIds.has(sid);
        const matchSearch = !q || row.dataset.search.includes(q);
        const matchKelas = !kelas || row.dataset.kelas === kelas;

        if (isTaken && !row.querySelector('input').checked) {
            row.style.display = 'none';
        } else {
            row.style.display = (matchSearch && matchKelas) ? '' : 'none';
        }
    });
}

// Refresh visibility across all groups (called when any checkbox changes or group removed)
function refreshAllGroupVisibility() {
    document.querySelectorAll('[id^="grp"]').forEach(grpEl => {
        const gn = grpEl.id.replace('grp', '');
        applyGroupFilter(gn);
        updateGroupCount(gn);
    });
}

function onGroupStudentChange() {
    refreshAllGroupVisibility();
}

function toggleAllGroupStudents(n, checked) {
    document.querySelectorAll('#grp' + n + ' .gs-row').forEach(row => {
        if (row.style.display !== 'none') {
            row.querySelector('input').checked = checked;
        }
    });
    refreshAllGroupVisibility();
}

function updateGroupCount(n) {
    const count = document.querySelectorAll('.gs-cb-' + n + ':checked').length;
    const el = document.querySelector('.gs-count-' + n);
    if (el) el.textContent = count + ' siswa';
}

// Load existing custom groups
existingGroups.forEach(g => addGroup(g.nama, g.student_ids));

// Toggle kelompok tes section based on peserta
document.querySelector('select[name="peserta_ujian"]').addEventListener('change', function() {
    document.getElementById('kelompokTesSection').style.display = this.value === 'Siswa' ? '' : 'none';
});
</script>
@endsection

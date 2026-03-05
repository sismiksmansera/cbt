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
        <form action="{{ route('admin.exam-sessions.unlock-all', $session->id) }}" method="POST">@csrf
            <button class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;" onclick="return confirm('Buka kunci semua siswa yang terkunci?')">
                <i class="fas fa-unlock"></i> Buka Semua Kunci
            </button>
        </form>
        <form action="{{ route('admin.exam-sessions.update-max-attempts', $session->id) }}" method="POST" style="display:flex;align-items:center;gap:6px;">@csrf
            <label style="font-size:12px;color:var(--text-secondary);white-space:nowrap;"><i class="fas fa-sign-in-alt"></i> Maks Login:</label>
            <input type="number" name="max_login_attempts" value="{{ $session->max_login_attempts ?? 1 }}" min="1" max="99"
                   style="width:52px;padding:5px 8px;border-radius:6px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-primary);font-size:13px;text-align:center;">
            <button class="btn btn-sm" style="background:var(--primary);color:#fff;padding:5px 10px;font-size:12px;">
                <i class="fas fa-save"></i>
            </button>
        </form>
        <a href="{{ route('admin.exam-sessions.export-results', $session->id) }}" class="btn btn-sm" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;text-decoration:none;">
            <i class="fas fa-file-excel"></i> Download Hasil
        </a>
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
    <div class="stat-card" data-filter="all" onclick="filterStudents('all')" style="cursor:pointer;transition:all .2s;">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-value">{{ $total }}</div><div class="stat-label">Total Peserta</div></div>
    </div>
    <div class="stat-card" data-filter="belum_mulai" onclick="filterStudents('belum_mulai')" style="cursor:pointer;transition:all .2s;">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div><div class="stat-value">{{ $waiting }}</div><div class="stat-label">Belum Mulai</div></div>
    </div>
    <div class="stat-card" data-filter="mengerjakan" onclick="filterStudents('mengerjakan')" style="cursor:pointer;transition:all .2s;">
        <div class="stat-icon purple"><i class="fas fa-pencil-alt"></i></div>
        <div><div class="stat-value">{{ $working }}</div><div class="stat-label">Mengerjakan</div></div>
    </div>
    <div class="stat-card" data-filter="selesai" onclick="filterStudents('selesai')" style="cursor:pointer;transition:all .2s;">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div><div class="stat-value">{{ $done }}</div><div class="stat-label">Selesai</div></div>
    </div>
    @if($locked > 0)
    <div class="stat-card" data-filter="terkunci" onclick="filterStudents('terkunci')" style="border-color:rgba(239,68,68,0.3);cursor:pointer;transition:all .2s;">
        <div class="stat-icon" style="background:rgba(239,68,68,0.15);"><i class="fas fa-lock" style="color:var(--danger);"></i></div>
        <div><div class="stat-value" style="color:var(--danger);">{{ $locked }}</div><div class="stat-label">Terkunci</div></div>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header" style="flex-wrap:wrap;gap:8px;">
        <h3>Status Peserta</h3>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            @if($groups->count() > 0)
            <select id="group-filter" onchange="filterGroup(this.value)" style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text-primary);font-size:13px;min-width:160px;">
                <option value="all" style="background:#fff;color:#333;">Semua Kelompok</option>
                @foreach($groups->sortBy('nama_kelompok') as $group)
                    <option value="{{ $group->nama_kelompok }}" style="background:#fff;color:#333;">{{ $group->nama_kelompok }}</option>
                @endforeach
            </select>
            @endif
            <span id="filter-badge" style="display:none;font-size:12px;padding:4px 12px;border-radius:20px;background:rgba(124,58,237,0.15);color:#7c3aed;font-weight:600;cursor:pointer;" onclick="filterStudents('all')">
                <span id="filter-text"></span> &times;
            </span>
        </div>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th style="width:36px;"><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)" title="Pilih semua"></th><th>Nama</th><th>NISN</th><th>Kelas</th><th>Kelompok</th><th>Mapel</th><th>Status</th><th>Login</th><th>Waktu Mulai</th><th>Waktu Selesai</th><th>Lihat</th><th>Aksi</th></tr></thead>
            <tbody id="student-tbody">
                @foreach($sessionStudents as $ss)
                <tr class="student-row" data-status="{{ $ss->status }}" data-locked="{{ $ss->is_locked ? '1' : '0' }}" data-group="{{ $studentGroupMap[$ss->student_id] ?? '' }}" style="{{ $ss->is_locked ? 'background:rgba(239,68,68,0.05);' : '' }}">
                    <td><input type="checkbox" class="student-checkbox" value="{{ $ss->student_id }}" onchange="updateResetBar()"></td>
                    <td style="font-weight:600;">
                        {{ $ss->student->nama ?? '-' }}
                        @if($ss->is_locked)
                            <span class="badge badge-danger" style="font-size:10px;margin-left:4px;"><i class="fas fa-lock"></i> TERKUNCI</span>
                        @endif
                    </td>
                    <td>{{ $ss->student->nisn ?? '-' }}</td>
                    <td>{{ $ss->student->kelas ?? '-' }}</td>
                    <td>{{ $studentGroupMap[$ss->student_id] ?? '-' }}</td>
                    <td style="font-size:13px;">{{ $studentMapelMap[$ss->student_id] ?? '-' }}</td>
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
                        @if($ss->status === 'mengerjakan' || $ss->status === 'selesai')
                            <a href="{{ route('admin.results.student-detail', [$session->id, $ss->student_id]) }}" target="_blank" class="btn btn-sm" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;" title="Lihat ujian siswa">
                                <i class="fas fa-eye"></i>
                            </a>
                        @else
                            <span class="btn btn-sm" style="opacity:0.3;cursor:default;background:var(--border);color:var(--text-secondary);" title="Belum mulai ujian">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="display:inline-flex;gap:4px;flex-wrap:wrap;">
                        @if($ss->is_locked)
                            <form action="{{ route('admin.exam-sessions.unlock-student', [$session->id, $ss->student_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-success btn-sm" onclick="return confirm('Buka kunci siswa ini?')"><i class="fas fa-unlock"></i> Buka Kunci</button>
                            </form>
                        @else
                            <form action="{{ route('admin.exam-sessions.lock-student', [$session->id, $ss->student_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-sm" style="background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;" onclick="return confirm('Kunci siswa ini? Siswa tidak akan bisa melanjutkan ujian.')"><i class="fas fa-lock"></i> Kunci</button>
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

{{-- Reset bar --}}
<div id="reset-bar" style="display:none;position:sticky;bottom:0;left:0;right:0;padding:12px 20px;background:var(--card-bg);border-top:2px solid var(--border);z-index:100;">
    <form id="reset-form" action="{{ route('admin.exam-sessions.reset-students', $session->id) }}" method="POST" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        @csrf
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:14px;font-weight:600;"><span id="selected-count">0</span> siswa dipilih</span>
            <button type="button" onclick="submitReset(false)" class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">
                <i class="fas fa-undo"></i> Reset Terpilih
            </button>
        </div>
        <button type="button" onclick="submitReset(true)" class="btn btn-sm" style="background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff;">
            <i class="fas fa-sync-alt"></i> Reset Semua
        </button>
    </form>
</div>

<script>
let activeFilter = 'all';
let activeGroup = 'all';

function applyFilters() {
    const rows = document.querySelectorAll('.student-row');
    let visibleCount = 0;
    rows.forEach(row => {
        const status = row.dataset.status;
        const isLocked = row.dataset.locked === '1';
        const group = row.dataset.group || '';

        let statusMatch = false;
        if (activeFilter === 'all') {
            statusMatch = true;
        } else if (activeFilter === 'terkunci') {
            statusMatch = isLocked;
        } else {
            statusMatch = (status === activeFilter);
        }

        let groupMatch = (activeGroup === 'all') || (group === activeGroup);

        const show = statusMatch && groupMatch;
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
}

function filterStudents(filter) {
    activeFilter = filter;
    const cards = document.querySelectorAll('.stat-card');
    const badge = document.getElementById('filter-badge');
    const filterText = document.getElementById('filter-text');

    cards.forEach(card => {
        if (card.dataset.filter === filter) {
            card.style.outline = '2px solid #7c3aed';
            card.style.outlineOffset = '-2px';
            card.style.transform = 'scale(1.03)';
        } else {
            card.style.outline = 'none';
            card.style.transform = 'scale(1)';
        }
    });

    if (filter === 'all') {
        badge.style.display = 'none';
    } else {
        const labels = {
            'belum_mulai': 'Belum Mulai',
            'mengerjakan': 'Mengerjakan',
            'selesai': 'Selesai',
            'terkunci': 'Terkunci'
        };
        filterText.textContent = 'Filter: ' + (labels[filter] || filter);
        badge.style.display = 'inline-block';
    }

    applyFilters();
}

function filterGroup(group) {
    activeGroup = group;
    applyFilters();
}

function toggleSelectAll(el) {
    const rows = document.querySelectorAll('.student-row');
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.student-checkbox');
            if (cb) cb.checked = el.checked;
        }
    });
    updateResetBar();
}

function updateResetBar() {
    const checked = document.querySelectorAll('.student-checkbox:checked');
    const bar = document.getElementById('reset-bar');
    const count = document.getElementById('selected-count');
    count.textContent = checked.length;
    bar.style.display = checked.length > 0 ? 'block' : 'none';
}

function submitReset(resetAll) {
    const form = document.getElementById('reset-form');
    // Remove old hidden inputs
    form.querySelectorAll('input[name="student_ids[]"], input[name="reset_all"]').forEach(e => e.remove());

    if (resetAll) {
        if (!confirm('PERHATIAN: Reset SEMUA siswa di sesi ini? Semua jawaban, hasil, dan riwayat ujian akan dihapus!')) return;
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'reset_all'; input.value = '1';
        form.appendChild(input);
    } else {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        if (checked.length === 0) { alert('Pilih siswa yang ingin direset.'); return; }
        if (!confirm('Reset ' + checked.length + ' siswa terpilih? Jawaban dan hasil ujian mereka akan dihapus.')) return;
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'student_ids[]'; input.value = cb.value;
            form.appendChild(input);
        });
    }
    form.submit();
}
</script>
@endsection

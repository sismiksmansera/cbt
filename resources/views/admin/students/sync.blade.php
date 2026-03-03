@extends('layouts.admin')
@section('title', 'Sinkronisasi Data SIMAS')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <h3><i class="fas fa-sync" style="color:var(--info);margin-right:8px;"></i>Sinkronisasi Siswa dari SIMAS</h3>
    </div>
    <div class="card-body-padded">
        <div style="padding:16px;border-radius:12px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.15);color:#60a5fa;font-size:13px;margin-bottom:24px;">
            <i class="fas fa-info-circle"></i>
            Sinkronisasi akan mengambil data siswa dari database <strong>simas_db</strong> dan menyimpannya ke database CBT.
            Kolom <strong>kelas</strong> akan diambil dari rombel semester yang sesuai dengan semester saat ini.
            Password default siswa adalah NISN mereka.
        </div>

        <div class="stat-grid" style="margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-database"></i></div>
                <div>
                    <div class="stat-value">{{ $simasStudents->sum('jumlah') }}</div>
                    <div class="stat-label">Siswa aktif di SIMAS</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-value">{{ $cbtTotal }}</div>
                    <div class="stat-label">Siswa di CBT saat ini</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.students.sync-simas') }}" id="syncForm">
            @csrf

            <div class="form-group">
                <label>Pilih Angkatan yang Akan Disinkronkan</label>
                <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
                    @foreach($simasStudents as $row)
                    @php
                        $now = now();
                        $yearsElapsed = $now->year - $row->angkatan_masuk;
                        $semester = $now->month >= 7 ? ($yearsElapsed * 2) + 1 : $yearsElapsed * 2;
                        $semester = max(1, min(6, $semester));
                        $tingkat = ceil($semester / 2);
                        $tingkatLabel = $tingkat == 1 ? 'X' : ($tingkat == 2 ? 'XI' : 'XII');
                    @endphp
                    <label style="display:flex;align-items:center;gap:12px;padding:16px;border-radius:12px;border:1px solid var(--border-color);cursor:pointer;transition:all 0.2s;background:rgba(15,23,42,0.3);"
                           onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        <input type="checkbox" name="angkatan[]" value="{{ $row->angkatan_masuk }}" checked style="width:18px;height:18px;">
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;">Angkatan {{ $row->angkatan_masuk }}</div>
                            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">
                                Kelas {{ $tingkatLabel }} • Semester {{ $semester }} •
                                <span class="badge badge-info" style="font-size:11px;">{{ $row->jumlah }} siswa</span>
                                • Rombel dari <code style="font-size:11px;color:var(--primary-light);">rombel_semester_{{ $semester }}</code>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="padding:14px;border-radius:12px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.15);color:var(--warning);font-size:13px;margin-bottom:24px;">
                <i class="fas fa-exclamation-triangle"></i>
                Siswa yang sudah ada (berdasarkan NISN) akan <strong>diperbarui</strong> datanya (nama, kelas, jenis kelamin).
                Siswa baru akan ditambahkan dengan password = NISN.
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" id="syncBtn"><i class="fas fa-sync"></i> Mulai Sinkronisasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border-radius:24px;padding:48px;text-align:center;border:1px solid var(--border-color);box-shadow:0 25px 60px rgba(0,0,0,0.5);max-width:400px;width:90%;">
        <div style="margin-bottom:24px;">
            <div class="sync-spinner"></div>
        </div>
        <h3 style="font-size:20px;margin-bottom:8px;">Menyinkronkan Data...</h3>
        <p style="color:var(--text-secondary);font-size:14px;margin-bottom:20px;">Mengambil data siswa dari database SIMAS dan menyimpan ke CBT. Proses ini mungkin memakan waktu beberapa menit.</p>
        <div style="display:flex;justify-content:center;gap:24px;">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--info);" id="loadingTimer">00:00</div>
                <div style="font-size:11px;color:var(--text-secondary);">Waktu berjalan</div>
            </div>
        </div>
        <div style="margin-top:20px;padding:10px 16px;border-radius:10px;background:rgba(59,130,246,0.08);font-size:12px;color:#60a5fa;">
            <i class="fas fa-info-circle"></i> Jangan tutup halaman ini selama proses berlangsung
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .sync-spinner {
        width: 64px; height: 64px; margin: 0 auto;
        border: 4px solid var(--border-color);
        border-top: 4px solid var(--primary);
        border-right: 4px solid var(--info);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('scripts')
<script>
document.getElementById('syncForm').addEventListener('submit', function(e) {
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';

    document.getElementById('syncBtn').disabled = true;

    // Timer
    let seconds = 0;
    const timerEl = document.getElementById('loadingTimer');
    setInterval(function() {
        seconds++;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }, 1000);
});
</script>
@endsection

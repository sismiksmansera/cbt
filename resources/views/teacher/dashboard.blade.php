<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru | CBT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a; --bg-card: #1e293b; --bg-hover: #334155;
            --text: #f1f5f9; --text-secondary: #94a3b8;
            --primary: #10b981; --primary-hover: #059669;
            --border: #334155; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
        .navbar {
            background: var(--bg-card); border-bottom: 1px solid var(--border);
            padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;
        }
        .navbar h1 { font-size: 18px; font-weight: 700; }
        .navbar h1 i { color: var(--primary); margin-right: 8px; }
        .navbar .user-info { display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--text-secondary); }
        .btn { padding: 8px 16px; border-radius: 10px; border: none; cursor: pointer; font-family: 'Inter'; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .btn-danger { background: rgba(239,68,68,0.15); color: var(--danger); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .container { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
        .page-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        .page-subtitle { color: var(--text-secondary); font-size: 14px; margin-bottom: 28px; }
        .session-card {
            background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;
            padding: 24px; margin-bottom: 16px; transition: all 0.2s;
        }
        .session-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .session-name { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .session-info { display: flex; gap: 16px; flex-wrap: wrap; color: var(--text-secondary); font-size: 13px; margin-bottom: 16px; }
        .session-info span i { margin-right: 4px; }
        .session-actions { display: flex; gap: 8px; }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
        .badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
        .modal-backdrop { position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:1000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px); }
        .modal-backdrop.show { display:flex; }
        .modal-box { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;width:100%;max-width:420px;max-height:80vh;overflow-y:auto;animation:modalIn 0.2s ease; }
        @keyframes modalIn { from { opacity:0; transform:scale(0.95) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
        .group-option { display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;border:1px solid var(--border);cursor:pointer;color:var(--text);transition:all 0.2s;text-decoration:none;margin-bottom:8px; }
        .group-option:hover { border-color:var(--primary);background:rgba(16,185,129,0.06);transform:translateX(4px); }
        .group-option:last-child { margin-bottom:0; }
        .group-icon { width:40px;height:40px;border-radius:10px;background:rgba(16,185,129,0.12);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-chalkboard-teacher"></i> Panel Guru CBT</h1>
        <div class="user-info">
            <span><i class="fas fa-user"></i> {{ session('teacher_name') }}</span>
            <form action="{{ route('teacher.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Keluar</button>
            </form>
        </div>
    </div>

    <div class="container">
        <h2 class="page-title">Sesi Ujian Aktif</h2>
        <p class="page-subtitle">Pilih sesi ujian untuk mengkonfirmasi kehadiran siswa</p>

        @if(session('success'))
        <div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @forelse($activeSessions as $s)
        <div class="session-card">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div class="session-name"><i class="fas fa-file-alt" style="color:var(--primary);margin-right:6px;"></i> {{ $s->nama_sesi }}</div>
                <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
            </div>
            <div class="session-info">
                <span><i class="fas fa-clock"></i> {{ $s->durasi }} menit</span>
                <span><i class="fas fa-key"></i> Token: <strong style="color:var(--warning);">{{ $s->token }}</strong></span>
                <span><i class="fas fa-list"></i> {{ $s->categories->count() }} soal</span>
            </div>
            <div class="session-actions">
                @if($s->sessionGroups->count() > 0)
                <button type="button" class="btn btn-primary" onclick="openGroupModal({{ $s->id }})"><i class="fas fa-clipboard-check"></i> Konfirmasi Kehadiran</button>
                @else
                <a href="{{ route('teacher.attendance', $s->id) }}" class="btn btn-primary"><i class="fas fa-clipboard-check"></i> Konfirmasi Kehadiran</a>
                @endif
                <a href="{{ route('teacher.monitor', $s->id) }}" class="btn" style="background:rgba(59,130,246,0.15);color:#3b82f6;"><i class="fas fa-desktop"></i> Monitor</a>
            </div>
        </div>

        {{-- Group selection modal for this session --}}
        @if($s->sessionGroups->count() > 0)
        <div class="modal-backdrop" id="groupModal-{{ $s->id }}" onclick="if(event.target===this)this.classList.remove('show')">
            <div class="modal-box">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="font-size:16px;font-weight:700;"><i class="fas fa-layer-group" style="color:var(--primary);margin-right:8px;"></i>Pilih Kelompok Tes</h3>
                    <button onclick="document.getElementById('groupModal-{{ $s->id }}').classList.remove('show')" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:18px;padding:4px;"><i class="fas fa-times"></i></button>
                </div>
                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:16px;">Pilih kelompok untuk konfirmasi kehadiran sesi <strong style="color:var(--text);">{{ $s->nama_sesi }}</strong></p>
                @foreach($s->sessionGroups as $grp)
                <a href="{{ route('teacher.attendance', ['sessionId' => $s->id, 'kelas' => $grp->nama_kelompok]) }}" class="group-option">
                    <div class="group-icon"><i class="fas fa-users"></i></div>
                    <div style="flex:1;">
                        <div style="font-size:14px;font-weight:600;">{{ $grp->nama_kelompok }}</div>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">{{ $grp->students->count() }} siswa</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:12px;"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif
        @empty
        <div class="empty-state">
            <i class="fas fa-calendar-times" style="display:block;"></i>
            <p>Tidak ada sesi ujian yang aktif saat ini.</p>
        </div>
        @endforelse
    </div>

    <script>
    function openGroupModal(sessionId) {
        document.getElementById('groupModal-' + sessionId).classList.add('show');
    }
    </script>
</body>
</html>

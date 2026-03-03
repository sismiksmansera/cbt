<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Ujian | CBT</title>
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
        .container { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
        .breadcrumb { color: var(--text-secondary); font-size: 13px; margin-bottom: 20px; }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; margin-bottom: 20px; }
        .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-header h3 { font-size: 15px; font-weight: 700; }
        .card-body { padding: 20px; }
        .kelas-grid { display: flex; gap: 10px; flex-wrap: wrap; }
        .kelas-btn {
            padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 600;
            border: 1px solid var(--border); color: var(--text); background: var(--bg-hover); transition: all 0.2s;
        }
        .kelas-btn:hover, .kelas-btn.active { background: var(--primary); border-color: var(--primary); color: white; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        td { padding: 14px 16px; border-bottom: 1px solid var(--border); font-size: 14px; }
        tr:hover { background: rgba(255,255,255,0.02); }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
        .badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-danger { background: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-info { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
            padding: 16px 20px; display: flex; align-items: center; gap: 14px;
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .stat-icon.green { background: rgba(16,185,129,0.15); color: var(--success); }
        .stat-icon.orange { background: rgba(245,158,11,0.15); color: var(--warning); }
        .stat-icon.blue { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .stat-icon.red { background: rgba(239,68,68,0.15); color: var(--danger); }
        .stat-icon.purple { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .stat-value { font-size: 22px; font-weight: 800; }
        .stat-label { font-size: 11px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; }
        .success-msg {
            padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-desktop"></i> Monitor Ujian</h1>
    </div>

    <div class="container">
        <div style="margin-bottom:12px;">
            <a href="{{ route('teacher.dashboard') }}" style="color:var(--text-secondary);text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        <div class="breadcrumb">
            <a href="{{ route('teacher.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a> / Monitor: {{ $session->nama_sesi }}
        </div>

        @if(session('success'))
        <div class="success-msg"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if($lockedKelas)
        <div style="padding:12px 16px;border-radius:10px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);color:#60a5fa;margin-bottom:20px;font-size:13px;">
            <i class="fas fa-lock"></i> Anda mengawasi rombel <strong>{{ $lockedKelas }}</strong> untuk sesi ini.
        </div>
        @else
        <div style="margin-bottom:20px;">
            <button type="button" onclick="document.getElementById('rombelModal').style.display='flex'" class="btn" style="padding:12px 24px;font-size:14px;background:var(--primary);color:white;border:none;border-radius:10px;cursor:pointer;font-family:'Inter';font-weight:600;">
                <i class="fas fa-school" style="margin-right:6px;"></i>
                @if($kelas) Rombel: {{ $kelas }} @else Pilih Rombel @endif
            </button>
        </div>

        <!-- Modal Pilih Rombel -->
        <div id="rombelModal" style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);" onclick="if(event.target===this)this.style.display='none'">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:24px;width:90%;max-width:420px;animation:modalIn 0.2s ease;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <h3 style="font-size:16px;font-weight:700;"><i class="fas fa-school" style="color:var(--primary);margin-right:8px;"></i>Pilih Rombel</h3>
                    <button onclick="document.getElementById('rombelModal').style.display='none'" style="background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:18px;"><i class="fas fa-times"></i></button>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;">
                    @foreach($kelasList as $k)
                    <a href="{{ route('teacher.monitor', [$session->id, 'kelas' => $k]) }}" class="kelas-btn {{ $kelas == $k ? 'active' : '' }}" style="text-align:center;">{{ $k }}</a>
                    @endforeach
                </div>
            </div>
        </div>
        <style>@keyframes modalIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }</style>
        @endif

        @if($kelas && $sessionStudents->count() > 0)
        @php
            $total = $sessionStudents->count();
            $hadir = $attendanceMap->where('status', 'hadir')->count();
            $tidakHadir = $attendanceMap->where('status', 'tidak_hadir')->count();
            $working = $sessionStudents->where('status', 'mengerjakan')->count();
            $done = $sessionStudents->where('status', 'selesai')->count();
        @endphp

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                <div><div class="stat-value">{{ $total }}</div><div class="stat-label">Total Siswa</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div><div class="stat-value">{{ $hadir }}</div><div class="stat-label">Hadir</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-user-times"></i></div>
                <div><div class="stat-value">{{ $tidakHadir }}</div><div class="stat-label">Tidak Hadir</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-pencil-alt"></i></div>
                <div><div class="stat-value">{{ $working }}</div><div class="stat-label">Mengerjakan</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-value">{{ $done }}</div><div class="stat-label">Selesai</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Kelas {{ $kelas }}</h3>
                <a href="{{ route('teacher.attendance', [$session->id, 'kelas' => $kelas]) }}" style="color:var(--primary);font-size:13px;text-decoration:none;">
                    <i class="fas fa-edit"></i> Edit Kehadiran
                </a>
            </div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>NISN</th><th>Kehadiran</th><th>Status Ujian</th><th>Login</th><th>Waktu Mulai</th><th>Waktu Selesai</th></tr>
                    </thead>
                    <tbody>
                        @foreach($sessionStudents as $i => $ss)
                        @php $att = $attendanceMap->get($ss->student_id); @endphp
                        <tr style="{{ ($att && $att->status === 'tidak_hadir') ? 'opacity:0.5;' : '' }}">
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight:600;">
                                {{ $ss->student->nama ?? '-' }}
                                @if($ss->is_locked)
                                    <span class="badge badge-danger" style="font-size:9px;margin-left:4px;"><i class="fas fa-lock"></i> LOCK</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:var(--text-secondary);">{{ $ss->student->nisn ?? '-' }}</td>
                            <td>
                                @if($att)
                                    @if($att->status === 'hadir')
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Hadir</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> Tidak Hadir</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-secondary);font-size:12px;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($ss->status === 'mengerjakan')<span class="badge badge-warning"><i class="fas fa-pencil-alt" style="font-size:10px;"></i> Mengerjakan</span>
                                @elseif($ss->status === 'selesai')<span class="badge badge-success"><i class="fas fa-check" style="font-size:10px;"></i> Selesai</span>
                                @else<span class="badge badge-info">Belum Mulai</span>@endif
                            </td>
                            <td>
                                <span class="badge {{ ($ss->login_count ?? 0) > 1 ? 'badge-warning' : 'badge-info' }}" style="font-size:11px;">
                                    {{ $ss->login_count ?? 0 }}x
                                </span>
                            </td>
                            <td style="font-size:13px;color:var(--text-secondary);">{{ $ss->waktu_mulai ? \Carbon\Carbon::parse($ss->waktu_mulai)->format('H:i:s') : '-' }}</td>
                            <td style="font-size:13px;color:var(--text-secondary);">{{ $ss->waktu_selesai ? \Carbon\Carbon::parse($ss->waktu_selesai)->format('H:i:s') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif($kelas)
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px;">
                <p style="color:var(--text-secondary);">Tidak ada siswa kelas {{ $kelas }} dalam sesi ini.</p>
            </div>
        </div>
        @endif
    </div>
</body>
</html>

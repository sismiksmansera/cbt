<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Kehadiran | CBT</title>
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
        .container { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
        .breadcrumb { color: var(--text-secondary); font-size: 13px; margin-bottom: 20px; }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .page-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        .page-subtitle { color: var(--text-secondary); font-size: 14px; margin-bottom: 28px; }
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
        .btn { padding: 8px 16px; border-radius: 10px; border: none; cursor: pointer; font-family: 'Inter'; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-lg { padding: 14px 28px; font-size: 15px; border-radius: 14px; }
        .radio-group { display: flex; gap: 12px; }
        .radio-option { position: relative; }
        .radio-option input { display: none; }
        .radio-option label {
            display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid var(--border); transition: all 0.2s;
        }
        .radio-option input[value="hadir"]:checked + label { background: rgba(16,185,129,0.15); border-color: var(--success); color: var(--success); }
        .radio-option input[value="tidak_hadir"]:checked + label { background: rgba(239,68,68,0.15); border-color: var(--danger); color: var(--danger); }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
        .badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-danger { background: rgba(239,68,68,0.15); color: #ef4444; }
        .bulk-actions { display: flex; gap: 8px; }
        .bulk-btn {
            padding: 6px 14px; border-radius: 8px; border: none; cursor: pointer; font-family: 'Inter'; font-size: 12px; font-weight: 600; transition: all 0.2s;
        }
        .bulk-btn.hadir { background: rgba(16,185,129,0.15); color: var(--success); }
        .bulk-btn.tidak { background: rgba(239,68,68,0.15); color: var(--danger); }
        .bulk-btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-clipboard-check"></i> Konfirmasi Kehadiran</h1>
    </div>

    <div class="container">
        <div style="margin-bottom:12px;">
            <a href="{{ route('teacher.dashboard') }}" style="color:var(--text-secondary);text-decoration:none;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        <div class="breadcrumb">
            <a href="{{ route('teacher.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a> / {{ $session->nama_sesi }}
        </div>

        <h2 class="page-title">{{ $session->nama_sesi }}</h2>
        <p class="page-subtitle">Pilih rombel lalu konfirmasi kehadiran siswa</p>

        @if($lockedKelas)
        <div style="padding:12px 16px;border-radius:10px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);color:#60a5fa;margin-bottom:20px;font-size:13px;">
            <i class="fas fa-lock"></i> Anda telah dikunci di rombel <strong>{{ $lockedKelas }}</strong> untuk sesi ini.
        </div>
        @else
        <div style="margin-bottom:20px;">
            <button type="button" onclick="document.getElementById('rombelModal').style.display='flex'" class="btn btn-primary" style="padding:12px 24px;font-size:14px;">
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
                    <a href="{{ route('teacher.attendance', [$session->id, 'kelas' => $k]) }}" class="kelas-btn {{ $kelas == $k ? 'active' : '' }}" style="text-align:center;">{{ $k }}</a>
                    @endforeach
                </div>
            </div>
        </div>
        <style>@keyframes modalIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }</style>
        @endif

        @if($kelas && $students->count() > 0)
        <form action="{{ route('teacher.save-attendance', $session->id) }}" method="POST">
            @csrf
            <input type="hidden" name="kelas" value="{{ $kelas }}">
            <div class="card">
                <div class="card-header">
                    <h3>Siswa Kelas {{ $kelas }} ({{ $students->count() }} siswa)</h3>
                    <div class="bulk-actions">
                        <button type="button" class="bulk-btn hadir" onclick="setAllStatus('hadir')"><i class="fas fa-check-double"></i> Semua Hadir</button>
                        <button type="button" class="bulk-btn tidak" onclick="setAllStatus('tidak_hadir')"><i class="fas fa-times"></i> Semua Tidak Hadir</button>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <table>
                        <thead>
                            <tr><th>No</th><th>Nama</th><th>NISN</th><th>Kehadiran</th></tr>
                        </thead>
                        <tbody>
                            @foreach($students as $i => $student)
                            @php $att = $existingAttendance->get($student->id); $status = ($att && $att->status === 'tidak_hadir') ? 'tidak_hadir' : 'hadir'; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="font-weight:600;">{{ $student->nama }}</td>
                                <td style="font-size:13px;color:var(--text-secondary);">{{ $student->nisn }}</td>
                                <td>
                                    <input type="hidden" name="attendance[{{ $student->id }}]" id="att_{{ $student->id }}" value="{{ $status }}">
                                    <button type="button" onclick="openAttModal({{ $student->id }}, '{{ addslashes($student->nama) }}')"
                                        id="btn_{{ $student->id }}"
                                        class="badge {{ $status === 'hadir' ? 'badge-success' : 'badge-danger' }}"
                                        style="cursor:pointer;border:none;padding:6px 14px;font-size:12px;">
                                        <i class="fas {{ $status === 'hadir' ? 'fa-check' : 'fa-times' }}"></i>
                                        {{ $status === 'hadir' ? 'Hadir' : 'Tidak Hadir' }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="text-align:right;margin-top:16px;">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save" style="margin-right:6px;"></i> Simpan Kehadiran</button>
            </div>
        </form>

        <!-- Modal Pilih Kehadiran -->
        <div id="attModal" style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);" onclick="if(event.target===this)this.style.display='none'">
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:28px;width:90%;max-width:340px;animation:modalIn 0.2s ease;text-align:center;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:6px;" id="attModalName"></h3>
                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:20px;">Pilih status kehadiran</p>
                <div style="display:flex;gap:12px;justify-content:center;">
                    <button type="button" onclick="setAttendance('hadir')" style="padding:14px 28px;border-radius:14px;border:none;cursor:pointer;font-family:'Inter';font-size:14px;font-weight:700;background:rgba(16,185,129,0.15);color:var(--success);transition:all 0.2s;">
                        <i class="fas fa-check" style="margin-right:6px;"></i> Hadir
                    </button>
                    <button type="button" onclick="setAttendance('tidak_hadir')" style="padding:14px 28px;border-radius:14px;border:none;cursor:pointer;font-family:'Inter';font-size:14px;font-weight:700;background:rgba(239,68,68,0.15);color:var(--danger);transition:all 0.2s;">
                        <i class="fas fa-times" style="margin-right:6px;"></i> Tidak Hadir
                    </button>
                </div>
            </div>
        </div>

        @elseif($kelas)
        <div class="card">
            <div class="card-body" style="text-align:center;padding:40px;">
                <i class="fas fa-users-slash" style="font-size:32px;color:var(--text-secondary);opacity:0.3;display:block;margin-bottom:12px;"></i>
                <p style="color:var(--text-secondary);">Tidak ada siswa kelas {{ $kelas }} yang terdaftar dalam sesi ini.</p>
            </div>
        </div>
        @endif
    </div>

    <script>
    let currentStudentId = null;

    function openAttModal(id, nama) {
        currentStudentId = id;
        document.getElementById('attModalName').textContent = nama;
        document.getElementById('attModal').style.display = 'flex';
    }

    function setAttendance(value) {
        document.getElementById('att_' + currentStudentId).value = value;
        const btn = document.getElementById('btn_' + currentStudentId);
        if (value === 'hadir') {
            btn.className = 'badge badge-success';
            btn.innerHTML = '<i class="fas fa-check"></i> Hadir';
        } else {
            btn.className = 'badge badge-danger';
            btn.innerHTML = '<i class="fas fa-times"></i> Tidak Hadir';
        }
        btn.style.cssText = 'cursor:pointer;border:none;padding:6px 14px;font-size:12px;';
        document.getElementById('attModal').style.display = 'none';
    }

    function setAllStatus(value) {
        document.querySelectorAll('input[id^="att_"]').forEach(inp => {
            inp.value = value;
            const id = inp.id.replace('att_', '');
            const btn = document.getElementById('btn_' + id);
            if (value === 'hadir') {
                btn.className = 'badge badge-success';
                btn.innerHTML = '<i class="fas fa-check"></i> Hadir';
            } else {
                btn.className = 'badge badge-danger';
                btn.innerHTML = '<i class="fas fa-times"></i> Tidak Hadir';
            }
            btn.style.cssText = 'cursor:pointer;border:none;padding:6px 14px;font-size:12px;';
        });
    }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfirmasi Ujian | CBT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a; --bg-card: #1e293b;
            --primary: #3b82f6; --primary-dark: #2563eb;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --text: #f1f5f9; --text-sec: #94a3b8; --border: #334155;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .confirm-container {
            max-width: 560px; width: 100%;
        }
        .confirm-card {
            background: var(--bg-card); border-radius: 20px;
            border: 1px solid var(--border); padding: 36px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .confirm-header {
            text-align: center; margin-bottom: 28px;
            padding-bottom: 20px; border-bottom: 1px solid var(--border);
        }
        .confirm-icon {
            width: 64px; height: 64px; border-radius: 16px;
            background: rgba(59,130,246,0.15); display: flex;
            align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 28px; color: var(--primary);
        }
        .confirm-header h2 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .confirm-header p { font-size: 13px; color: var(--text-sec); }

        .info-section { margin-bottom: 20px; }
        .info-section-title {
            font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-sec); font-weight: 700; margin-bottom: 10px;
        }
        .info-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
        }
        .info-item {
            padding: 14px 16px; border-radius: 12px;
            background: rgba(15,23,42,0.5); border: 1px solid var(--border);
        }
        .info-item.full { grid-column: 1 / -1; }
        .info-label { font-size: 11px; color: var(--text-sec); margin-bottom: 4px; }
        .info-value { font-size: 15px; font-weight: 700; }

        .subject-list { margin-bottom: 24px; }
        .subject-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px; border-radius: 12px;
            background: rgba(15,23,42,0.5); border: 1px solid var(--border);
            margin-bottom: 8px;
        }
        .subject-name { font-size: 14px; font-weight: 600; }
        .subject-info { font-size: 12px; color: var(--text-sec); }
        .subject-badge {
            font-size: 11px; font-weight: 700; padding: 4px 10px;
            border-radius: 6px; background: rgba(59,130,246,0.15); color: var(--primary);
        }

        .rules-box {
            padding: 16px; border-radius: 12px; margin-bottom: 24px;
            background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);
        }
        .rules-box h4 { font-size: 13px; color: var(--danger); margin-bottom: 8px; }
        .rules-box ul { list-style: none; padding: 0; }
        .rules-box li {
            font-size: 12px; color: var(--text-sec); padding: 3px 0;
            display: flex; align-items: center; gap: 8px;
        }
        .rules-box li i { color: var(--danger); font-size: 10px; width: 14px; }

        .btn-start {
            width: 100%; padding: 16px; border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; font-size: 16px; font-weight: 700;
            font-family: 'Inter', sans-serif; border: none;
            cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 4px 15px rgba(59,130,246,0.3);
        }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59,130,246,0.4); }
        .btn-start:active { transform: translateY(0); }

        @media (max-width: 480px) {
            .confirm-card { padding: 24px; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-card">
            <div class="confirm-header">
                <div class="confirm-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h2>Konfirmasi Ujian</h2>
                <p>Periksa informasi berikut sebelum memulai ujian</p>
            </div>

            <div class="info-section">
                <div class="info-section-title">Informasi Peserta</div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama</div>
                        <div class="info-value">{{ $student->nama }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NISN</div>
                        <div class="info-value">{{ $student->nisn }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $student->kelas ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Durasi Ujian</div>
                        <div class="info-value" style="color: var(--warning);">{{ $durasi }} Menit</div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="info-section-title">Sesi Ujian</div>
                <div class="info-item full" style="margin-bottom: 12px;">
                    <div class="info-label">Nama Sesi</div>
                    <div class="info-value">{{ $session->nama_sesi }}</div>
                </div>
            </div>

            <div class="info-section">
                <div class="info-section-title">Mata Pelajaran</div>
                <div class="subject-list">
                    @foreach($categories as $cat)
                    <div class="subject-item">
                        <div>
                            <div class="subject-name">{{ $cat['nama'] }}</div>
                            <div class="subject-info">{{ $cat['jumlah_soal'] }} soal</div>
                        </div>
                        <span class="subject-badge">
                            <i class="fas fa-book"></i> {{ $cat['mode'] === 'sebagian' ? 'Acak' : 'Semua' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rules-box">
                <h4><i class="fas fa-exclamation-triangle"></i> Peraturan Ujian</h4>
                <ul>
                    <li><i class="fas fa-circle"></i> Dilarang membuka aplikasi/tab lain saat ujian berlangsung</li>
                    <li><i class="fas fa-circle"></i> Dilarang membuka notifikasi atau mode layar ganda</li>
                    <li><i class="fas fa-circle"></i> Ujian akan <strong style="color:var(--danger);">terkunci otomatis</strong> jika terdeteksi aktivitas di luar ujian</li>
                    <li><i class="fas fa-circle"></i> Jawaban tersimpan otomatis, tidak perlu khawatir kehilangan data</li>
                </ul>
            </div>

            <form action="{{ route('student.exam.start') }}" method="POST" id="startForm">
                @csrf
                <button type="submit" class="btn-start" id="btnStart">
                    <i class="fas fa-play-circle"></i> Mulai Ujian
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('startForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnStart');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memulai ujian...';
        });
    </script>
</body>
</html>

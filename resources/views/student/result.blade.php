<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian | CBT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a, #1e1b4b);
            padding: 20px; position: relative;
        }
        .result-card {
            background: rgba(30,41,59,0.8); backdrop-filter: blur(24px);
            border-radius: 28px; padding: 48px; max-width: 480px; width: 100%;
            border: 1px solid rgba(99,102,241,0.2);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            text-align: center; animation: slideUp 0.6s ease;
        }
        @keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .result-icon { font-size: 64px; margin-bottom: 20px; }
        .result-icon.pass { color: #10b981; }
        .result-icon.fail { color: #ef4444; }
        h2 { color: white; font-size: 24px; margin-bottom: 8px; }
        .exam-name { color: #94a3b8; font-size: 14px; margin-bottom: 32px; }
        .score-circle {
            width: 160px; height: 160px; border-radius: 50%; margin: 0 auto 32px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            position: relative;
        }
        .score-circle.pass {
            background: radial-gradient(circle, rgba(16,185,129,0.15), transparent);
            border: 4px solid #10b981; box-shadow: 0 0 40px rgba(16,185,129,0.2);
        }
        .score-circle.fail {
            background: radial-gradient(circle, rgba(239,68,68,0.15), transparent);
            border: 4px solid #ef4444; box-shadow: 0 0 40px rgba(239,68,68,0.2);
        }
        .score-value { font-size: 48px; font-weight: 800; color: white; line-height: 1; }
        .score-label { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        .status-badge {
            display: inline-block; padding: 8px 24px; border-radius: 10px;
            font-size: 14px; font-weight: 700; margin-bottom: 28px;
            letter-spacing: 1px;
        }
        .status-badge.pass { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); }
        .status-badge.fail { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px;
        }
        .stat-box {
            padding: 16px; border-radius: 14px; background: rgba(15,23,42,0.5);
            border: 1px solid #334155;
        }
        .stat-box-value { font-size: 24px; font-weight: 800; color: white; }
        .stat-box-label { font-size: 11px; color: #94a3b8; }
        .btn-logout {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 12px; border: none;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white; font-size: 14px; font-weight: 600; font-family: 'Inter';
            cursor: pointer; transition: all 0.3s; text-decoration: none;
        }
        .btn-logout:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.4); }
    </style>
</head>
<body>
    <div class="result-card">
        @if($result)
            <div class="result-icon {{ $result->lulus ? 'pass' : 'fail' }}">
                <i class="fas {{ $result->lulus ? 'fa-trophy' : 'fa-times-circle' }}"></i>
            </div>
            <h2>{{ $result->lulus ? 'Selamat! Kamu Lulus!' : 'Belum Lulus' }}</h2>
            <p class="exam-name">{{ $session->nama_sesi }}</p>

            <div class="score-circle {{ $result->lulus ? 'pass' : 'fail' }}">
                <div class="score-value">{{ number_format($result->skor, 0) }}</div>
                <div class="score-label">SKOR</div>
            </div>

            <div class="status-badge {{ $result->lulus ? 'pass' : 'fail' }}">
                {{ $result->lulus ? '✓ LULUS' : '✗ TIDAK LULUS' }}
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-box-value">{{ $result->total_soal }}</div>
                    <div class="stat-box-label">Total Soal</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-value">{{ $result->dijawab }}</div>
                    <div class="stat-box-label">Dijawab</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-value" style="color:#10b981;">{{ $result->benar }}</div>
                    <div class="stat-box-label">Benar</div>
                </div>
            </div>
        @else
            <div class="result-icon" style="color:#f59e0b;"><i class="fas fa-check-circle"></i></div>
            <h2>Ujian Selesai</h2>
            <p class="exam-name">Jawaban kamu telah dikumpulkan.</p>
        @endif

        <form action="{{ route('student.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
        </form>
    </div>
</body>
</html>

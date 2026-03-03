<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBT | SMAN 1 Seputih Raman</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1a1a2e 50%, #16213e 100%);
            padding: 20px; position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 20% 30%, rgba(59,130,246,0.1), transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16,185,129,0.08), transparent 40%),
                        radial-gradient(circle at 50% 50%, rgba(139,92,246,0.06), transparent 50%);
        }
        .orbs { position: absolute; inset: 0; pointer-events: none; z-index: 1; }
        .orb { position: absolute; border-radius: 50%; animation: orbit 25s infinite linear; }
        .orb:nth-child(1) { width: 300px; height: 300px; top: -100px; right: -100px; background: radial-gradient(circle, rgba(99,102,241,0.08), transparent); }
        .orb:nth-child(2) { width: 200px; height: 200px; bottom: -50px; left: -50px; background: radial-gradient(circle, rgba(16,185,129,0.08), transparent); animation-duration: 30s; }
        @keyframes orbit { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .wrapper {
            width: 100%; max-width: 520px; position: relative; z-index: 10;
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .main-box {
            background: rgba(30, 41, 59, 0.75); backdrop-filter: blur(24px); border-radius: 28px;
            padding: 48px 40px; border: 1px solid rgba(59,130,246,0.15);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4); position: relative; overflow: hidden;
        }
        .main-box::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #3b82f6, #10b981, #8b5cf6, #3b82f6);
            background-size: 300%; animation: shimmer 4s ease infinite;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .header { text-align: center; margin-bottom: 36px; }
        .logo { width: 100px; height: 100px; margin: 0 auto 20px; }
        .logo img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3)); }
        .header h2 { color: white; font-size: 26px; font-weight: 800; margin-bottom: 6px; }
        .header p { color: #94a3b8; font-size: 14px; }

        .role-grid { display: flex; flex-direction: column; gap: 12px; }
        .role-card {
            display: flex; align-items: center; gap: 16px;
            padding: 18px 22px; border-radius: 16px;
            background: rgba(15, 23, 42, 0.5); border: 1px solid #334155;
            text-decoration: none; color: white; transition: all 0.3s; position: relative; overflow: hidden;
        }
        .role-card::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
            transition: 0.5s;
        }
        .role-card:hover::after { left: 100%; }
        .role-card:hover { transform: translateX(6px); border-color: var(--c); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .role-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .role-info h3 { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .role-info p { font-size: 12px; color: #94a3b8; }
        .role-arrow { margin-left: auto; color: #475569; font-size: 16px; transition: all 0.3s; }
        .role-card:hover .role-arrow { color: white; transform: translateX(4px); }

        .role-card.admin { --c: #3b82f6; }
        .role-card.admin .role-icon { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .role-card.admin:hover { background: rgba(59,130,246,0.08); }

        .role-card.pengawas { --c: #10b981; }
        .role-card.pengawas .role-icon { background: rgba(16,185,129,0.15); color: #10b981; }
        .role-card.pengawas:hover { background: rgba(16,185,129,0.08); }

        .role-card.siswa { --c: #8b5cf6; }
        .role-card.siswa .role-icon { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .role-card.siswa:hover { background: rgba(139,92,246,0.08); }

        .footer { text-align: center; margin-top: 28px; color: #475569; font-size: 12px; }
        @media (max-width: 480px) { .main-box { padding: 32px 24px; border-radius: 20px; } }
    </style>
</head>
<body>
    <div class="orbs"><div class="orb"></div><div class="orb"></div></div>
    <div class="wrapper">
        <div class="main-box">
            <div class="header">
                <div class="logo"><img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo SMAN 1 Seputih Raman"></div>
                <h2>Ujian Online</h2>
                <p>SMAN 1 Seputih Raman</p>
            </div>

            <div class="role-grid">
                <a href="{{ route('admin.login') }}" class="role-card admin">
                    <div class="role-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="role-info">
                        <h3>Admin</h3>
                        <p>Kelola ujian, soal, dan data siswa</p>
                    </div>
                    <i class="fas fa-chevron-right role-arrow"></i>
                </a>
                <a href="{{ route('teacher.login') }}" class="role-card pengawas">
                    <div class="role-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="role-info">
                        <h3>Pengawas</h3>
                        <p>Konfirmasi kehadiran & monitoring ujian</p>
                    </div>
                    <i class="fas fa-chevron-right role-arrow"></i>
                </a>
                <a href="{{ route('student.login') }}" class="role-card siswa">
                    <div class="role-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="role-info">
                        <h3>Siswa</h3>
                        <p>Masuk untuk mengerjakan ujian</p>
                    </div>
                    <i class="fas fa-chevron-right role-arrow"></i>
                </a>
            </div>
        </div>
        <div class="footer">© {{ date('Y') }} SMAN 1 Seputih Raman — Computer Based Test</div>
    </div>
</body>
</html>

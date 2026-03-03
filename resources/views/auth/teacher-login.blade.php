<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru | CBT</title>
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
            background: radial-gradient(circle at 30% 40%, rgba(16,185,129,0.12), transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(59,130,246,0.08), transparent 40%);
        }
        .login-wrapper { width: 100%; max-width: 440px; position: relative; z-index: 10; animation: slideUp 0.6s ease; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-box {
            background: rgba(30, 41, 59, 0.75); backdrop-filter: blur(24px); border-radius: 28px;
            padding: 48px 40px; border: 1px solid rgba(16,185,129,0.15);
            box-shadow: 0 30px 60px rgba(0,0,0,0.4); position: relative; overflow: hidden;
        }
        .login-box::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #10b981, #059669, #10b981);
            background-size: 200%; animation: shimmer 3s ease infinite;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .login-header { text-align: center; margin-bottom: 36px; }
        .logo-icon-wrap { width: 100px; height: 100px; margin: 0 auto 20px; }
        .logo-icon-wrap img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3)); }
        .login-header h2 { color: white; font-size: 28px; font-weight: 800; margin-bottom: 6px; }
        .login-header p { color: #94a3b8; font-size: 14px; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 16px; }
        .form-group input {
            width: 100%; padding: 15px 16px 15px 48px;
            background: rgba(15, 23, 42, 0.6); border: 1px solid #334155;
            border-radius: 14px; color: white; font-size: 15px; font-family: 'Inter';
            transition: all 0.2s;
        }
        .form-group input::placeholder { color: #475569; }
        .form-group input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
        .btn-login {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white; border: none; border-radius: 14px;
            font-size: 15px; font-weight: 700; font-family: 'Inter';
            cursor: pointer; transition: all 0.3s; text-transform: uppercase; letter-spacing: 1px;
            position: relative; overflow: hidden;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(16,185,129,0.4); }
        .error-box {
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: #f87171; padding: 13px 16px; border-radius: 12px;
            margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;
        }
        .footer { text-align: center; margin-top: 28px; color: #475569; font-size: 12px; }
        .links { text-align: center; margin-top: 16px; display: flex; justify-content: center; gap: 16px; }
        .links a { color: #64748b; font-size: 12px; text-decoration: none; transition: color 0.2s; }
        .links a:hover { color: #94a3b8; }
        @media (max-width: 480px) { .login-box { padding: 32px 24px; border-radius: 20px; } }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-icon-wrap"><img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo"></div>
                <h2>Panel Guru</h2>
                <p>SMAN 1 Seputih Raman</p>
            </div>
            @if($errors->any())
                <div class="error-box"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('teacher.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label><i class="fas fa-id-badge" style="margin-right:4px;"></i> NIP</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-tie"></i>
                        <input type="text" name="nip" placeholder="Masukkan NIP" value="{{ old('nip') }}" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock" style="margin-right:4px;"></i> Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key"></i>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt" style="margin-right:8px;"></i> Masuk</button>
            </form>
            <div class="footer">© {{ date('Y') }} SMAN 1 Seputih Raman</div>
            <div class="links">
                <a href="{{ route('student.login') }}"><i class="fas fa-user-graduate"></i> Login Siswa</a>
                <a href="{{ route('admin.login') }}"><i class="fas fa-cog"></i> Admin Panel</a>
            </div>
        </div>
    </div>
</body>
</html>

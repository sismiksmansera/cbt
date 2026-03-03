<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | CBT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            padding: 20px; position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 20% 50%, rgba(99,102,241,0.15), transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(139,92,246,0.1), transparent 40%);
            z-index: 1;
        }
        .particles { position: absolute; inset: 0; overflow: hidden; z-index: 2; pointer-events: none; }
        .particle {
            position: absolute; width: 6px; height: 6px;
            background: rgba(99,102,241,0.3); border-radius: 50%;
            animation: floatUp 20s infinite;
        }
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; width: 4px; height: 4px; }
        .particle:nth-child(2) { left: 25%; animation-delay: 3s; width: 8px; height: 8px; }
        .particle:nth-child(3) { left: 40%; animation-delay: 1s; }
        .particle:nth-child(4) { left: 55%; animation-delay: 5s; width: 5px; height: 5px; }
        .particle:nth-child(5) { left: 70%; animation-delay: 2s; width: 7px; height: 7px; }
        .particle:nth-child(6) { left: 85%; animation-delay: 4s; }
        @keyframes floatUp {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; transform: translateY(80vh) scale(1); }
            90% { opacity: 0.5; }
            100% { transform: translateY(-10vh) scale(0.5); opacity: 0; }
        }
        .login-wrapper {
            width: 100%; max-width: 420px; position: relative; z-index: 10;
            animation: slideUp 0.6s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-box {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-radius: 24px; padding: 48px 40px;
            border: 1px solid rgba(99,102,241,0.2);
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        }
        .login-box::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px; border-radius: 24px 24px 0 0;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #a78bfa, #8b5cf6, #6366f1);
            background-size: 200%; animation: shimmer 3s ease infinite;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .login-header { text-align: center; margin-bottom: 36px; }
        .logo-box {
            width: 72px; height: 72px; margin: 0 auto 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 800; color: white;
            box-shadow: 0 10px 30px rgba(99,102,241,0.3);
        }
        .login-header h2 { color: white; font-size: 26px; font-weight: 700; margin-bottom: 6px; }
        .login-header p { color: #94a3b8; font-size: 14px; }
        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; color: #94a3b8; font-size: 12px; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 16px; }
        .form-group input {
            width: 100%; padding: 14px 16px 14px 48px;
            background: rgba(15, 23, 42, 0.6); border: 1px solid #334155;
            border-radius: 12px; color: white; font-size: 14px; font-family: 'Inter';
            transition: all 0.2s;
        }
        .form-group input::placeholder { color: #475569; }
        .form-group input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .btn-login {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white; border: none; border-radius: 12px;
            font-size: 15px; font-weight: 600; font-family: 'Inter';
            cursor: pointer; transition: all 0.3s;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(99,102,241,0.4); }
        .error-box {
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: #f87171; padding: 12px 16px; border-radius: 12px;
            margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;
        }
        .footer { text-align: center; margin-top: 24px; color: #475569; font-size: 12px; }
        @media (max-width: 480px) { .login-box { padding: 32px 24px; } }
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
    </div>
    <div class="login-wrapper">
        <div class="login-box" style="position:relative;">
            <div class="login-header">
                <div class="logo-box">C</div>
                <h2>CBT Admin</h2>
                <p>Login ke panel administrasi</p>
            </div>
            @if($errors->any())
                <div class="error-box"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Masuk</button>
            </form>
            <div class="footer">© {{ date('Y') }} CBT - Computer Based Test</div>
        </div>
    </div>
</body>
</html>

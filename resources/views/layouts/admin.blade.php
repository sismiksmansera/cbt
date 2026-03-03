<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | CBT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    <script>
        function renderAllMath(el) {
            if (typeof renderMathInElement === 'undefined') return;
            renderMathInElement(el || document.body, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '\\(', right: '\\)', display: false},
                    {left: '$', right: '$', display: false}
                ],
                throwOnError: false
            });
        }
        document.addEventListener('DOMContentLoaded', function() { renderAllMath(); });
    </script>
    @yield('styles')
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #f59e0b;
            --bg-dark: #0f172a;
            --bg-sidebar: #1e293b;
            --bg-card: #1e293b;
            --bg-main: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            z-index: 100;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800; color: white;
        }
        .sidebar-header h1 { font-size: 20px; font-weight: 700; color: white; }
        .sidebar-header span { font-size: 11px; color: var(--text-secondary); }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 12px; }
        .nav-label {
            font-size: 10px; font-weight: 700; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 1.5px;
            padding: 12px 12px 8px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 10px;
            color: var(--text-secondary); text-decoration: none;
            font-size: 14px; font-weight: 500;
            transition: all 0.2s; margin-bottom: 2px;
        }
        .nav-item:hover { background: rgba(99,102,241,0.1); color: var(--text-primary); }
        .nav-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .nav-item i { width: 20px; text-align: center; font-size: 15px; }
        .nav-item .badge {
            margin-left: auto; background: var(--danger);
            color: white; font-size: 11px; font-weight: 700;
            padding: 2px 8px; border-radius: 10px;
        }
        /* Main */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }
        .top-bar {
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .top-bar h2 { font-size: 20px; font-weight: 700; }
        .top-bar-right { display: flex; align-items: center; gap: 16px; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: white;
        }
        .page-content { padding: 28px 32px; }
        .btn-menu-toggle {
            display: none;
            background: var(--primary); border: none; color: white;
            width: 38px; height: 38px; border-radius: 10px;
            font-size: 18px; cursor: pointer;
        }
        /* Cards */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; padding: 24px;
            display: flex; align-items: center; gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-icon.blue { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .stat-icon.green { background: rgba(16,185,129,0.15); color: #10b981; }
        .stat-icon.purple { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        .stat-icon.orange { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
        .stat-label { font-size: 13px; color: var(--text-secondary); margin-top: 4px; }
        /* Table */
        .card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; overflow: hidden;
        }
        .card-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .card-header h3 { font-size: 16px; font-weight: 700; }
        .card-body { padding: 0; }
        .card-body-padded { padding: 24px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left; padding: 14px 20px; font-size: 12px;
            font-weight: 600; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            background: rgba(15,23,42,0.5);
        }
        td {
            padding: 14px 20px; border-bottom: 1px solid var(--border-color);
            font-size: 14px; vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(99,102,241,0.04); }
        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px;
            font-size: 13px; font-weight: 600; font-family: 'Inter', sans-serif;
            border: none; cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.3); }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-warning { background: var(--warning); color: #1e293b; }
        .btn-outline {
            background: transparent; border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 8px; }
        .btn-icon { padding: 8px; width: 34px; height: 34px; justify-content: center; }
        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--text-secondary); margin-bottom: 8px;
        }
        .form-control {
            width: 100%; padding: 11px 16px; border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-main); color: var(--text-primary);
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .form-control::placeholder { color: var(--text-secondary); }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        textarea.form-control { min-height: 100px; resize: vertical; }
        /* Badges */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 8px;
            font-size: 12px; font-weight: 600;
        }
        .badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
        .badge-danger { background: rgba(239,68,68,0.15); color: #ef4444; }
        .badge-warning { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .badge-info { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .badge-purple { background: rgba(139,92,246,0.15); color: #8b5cf6; }
        /* Alert */
        .alert {
            padding: 14px 20px; border-radius: 12px;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
            font-size: 14px; font-weight: 500;
        }
        .alert-success { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
        .alert-danger { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
        /* Pagination */
        .pagination { display: flex; gap: 4px; padding: 20px; justify-content: center; }
        .pagination a, .pagination span {
            padding: 8px 14px; border-radius: 8px; font-size: 13px;
            text-decoration: none; color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .pagination .active span { background: var(--primary); color: white; border-color: var(--primary); }
        .pagination a:hover { background: rgba(99,102,241,0.1); border-color: var(--primary); }
        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
            z-index: 200; align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--bg-card); border-radius: 20px;
            width: 90%; max-width: 540px; max-height: 90vh;
            overflow-y: auto; box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            border: 1px solid var(--border-color);
        }
        .modal-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header h3 { font-size: 16px; font-weight: 700; }
        .modal-close {
            background: none; border: none; color: var(--text-secondary);
            font-size: 20px; cursor: pointer; padding: 4px;
        }
        .modal-body { padding: 24px; }
        .modal-footer {
            padding: 16px 24px; border-top: 1px solid var(--border-color);
            display: flex; justify-content: flex-end; gap: 10px;
        }
        /* Grid */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        /* Empty state */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--text-secondary);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 15px; }
        /* Search */
        .search-bar {
            display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
        }
        .search-bar .form-control { max-width: 280px; }
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .btn-menu-toggle { display: flex; align-items: center; justify-content: center; }
            .page-content { padding: 20px 16px; }
            .top-bar { padding: 12px 16px; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
            table { font-size: 13px; }
            th, td { padding: 10px 14px; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">C</div>
            <div>
                <h1>CBT</h1>
                <span>Computer Based Test</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <div class="nav-label">Manajemen Data</div>
            <a href="{{ route('admin.students.index') }}" class="nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i> Siswa
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="nav-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                <i class="fas fa-book"></i> Mata Pelajaran
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="nav-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i> Guru
            </a>

            <div class="nav-label">Ujian</div>
            <a href="{{ route('admin.exams.index') }}" class="nav-item {{ request()->routeIs('admin.exams.*') || request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Bank Soal
            </a>
            <a href="{{ route('admin.exam-sessions.index') }}" class="nav-item {{ request()->routeIs('admin.exam-sessions.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> Setting Ujian
            </a>
            <a href="{{ route('admin.exam-activities.index') }}" class="nav-item {{ request()->routeIs('admin.exam-activities.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Kegiatan Ujian
            </a>

            <div class="nav-label">Penilaian</div>
            <a href="{{ route('admin.results.index') }}" class="nav-item {{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Hasil Ujian
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <div class="top-bar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="btn-menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>@yield('title', 'Dashboard')</h2>
            </div>
            <div class="top-bar-right">
                <div class="user-info">
                    <div class="user-avatar">{{ substr(session('admin_name', 'A'), 0, 1) }}</div>
                    <div>
                        <div style="font-size:14px;font-weight:600;">{{ session('admin_name', 'Admin') }}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Administrator</div>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script>
        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 768 && sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) && !e.target.classList.contains('btn-menu-toggle')) {
                sidebar.classList.remove('open');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

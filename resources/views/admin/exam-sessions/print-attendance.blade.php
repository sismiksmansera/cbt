<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir - {{ $session->nama_sesi }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 15mm 15mm 15mm 15mm; }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: #f1f5f9;
        }

        .print-header-ui {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            border-radius: 14px;
            margin: 20px auto;
            max-width: 800px;
        }
        .print-header-ui h2 { font-size: 20px; font-weight: 700; }
        .print-header-ui p { opacity: 0.9; font-size: 13px; margin-top: 4px; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 24px; background: rgba(255,255,255,0.2); color: white;
            border: 2px solid rgba(255,255,255,0.4); border-radius: 10px;
            font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 12px;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-print:hover { background: rgba(255,255,255,0.3); }

        @media print {
            .print-header-ui { display: none !important; }
            body { background: white; }
        }

        .page {
            width: 180mm;
            margin: 0 auto;
            background: white;
            padding: 0;
        }

        /* KOP */
        .kop {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 6px;
            border-bottom: 3px double #000;
            margin-bottom: 16px;
        }
        .kop img {
            width: 55px;
            height: auto;
        }
        .kop-text {
            flex: 1;
            text-align: center;
            line-height: 1.3;
        }
        .kop-text .line1 {
            font-size: 11pt;
        }
        .kop-text .line2 {
            font-size: 10pt;
        }
        .kop-text .line3 {
            font-size: 15pt;
            font-weight: bold;
        }
        .kop-text .line4 {
            font-size: 8pt;
            color: #c00;
        }
        .kop-text .line5 {
            font-size: 8pt;
            font-style: italic;
        }
        .kop-text .line6 {
            font-size: 8pt;
        }

        /* Title */
        .title-section {
            text-align: center;
            margin-bottom: 16px;
        }
        .title-section h2 {
            font-size: 14pt;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .title-section .sub {
            font-size: 12pt;
            font-weight: bold;
        }

        /* Info */
        .info-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 11pt;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .info-table .label {
            width: 130px;
            font-weight: 600;
        }
        .info-table .sep {
            width: 15px;
            text-align: center;
        }

        /* Attendance table */
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 20px;
        }
        table.attendance th, table.attendance td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }
        table.attendance th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }
        table.attendance td.center {
            text-align: center;
        }
        table.attendance td.ttd {
            width: 80px;
        }

        /* Footer */
        .footer-section {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .footer-sign {
            text-align: center;
            font-size: 11pt;
            line-height: 1.5;
        }
        .footer-sign .ttd-space {
            height: 50px;
        }
        .footer-sign .dotted {
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="print-header-ui">
        <h2>📋 Daftar Hadir Ujian</h2>
        <p>{{ $session->nama_sesi }} — {{ count($students) }} peserta</p>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Daftar Hadir
        </button>
    </div>

    <div class="page">
        {{-- KOP --}}
        <div class="kop">
            <img src="{{ asset('images/logo-lampung.png') }}" alt="Logo Lampung">
            <div class="kop-text">
                <div class="line1">PEMERINTAH PROVINSI LAMPUNG</div>
                <div class="line2">DINAS PENDIDIKAN DAN KEBUDAYAAN</div>
                <div class="line3">SMA NEGERI 1 SEPUTIH RAMAN</div>
                <div class="line4">NSS. 301120207036 — NPSN 10802068 — AKREDITASI "A"</div>
                <div class="line5">Alamat : Jl. Raya Seputih Raman Kec. Seputih Raman Kab. Lampung Tengah</div>
                <div class="line6">Website : <u>www.sman1seputihraman.sch.id</u></div>
            </div>
            <img src="{{ asset('images/logo-sekolah.png') }}" alt="Logo Sekolah">
        </div>

        {{-- Title --}}
        <div class="title-section">
            <h2>Daftar Hadir</h2>
            <div class="sub">{{ $session->activity->nama_kegiatan ?? '-' }}</div>
        </div>

        {{-- Info --}}
        @php
            $tanggal = $session->waktu_mulai ? \Carbon\Carbon::parse($session->waktu_mulai)->translatedFormat('d F Y') : '-';
            $waktuMulai = $session->waktu_mulai ? \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i') : '-';
            $waktuSelesai = $session->waktu_selesai ? \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i') : '-';
        @endphp
        <table class="info-table">
            <tr>
                <td class="label">Nama Sesi</td>
                <td class="sep">:</td>
                <td>{{ $session->nama_sesi }}</td>
            </tr>
            <tr>
                <td class="label">Kelompok Tes</td>
                <td class="sep">:</td>
                <td>{{ $kelompokTes ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="sep">:</td>
                <td>{{ $tanggal }}</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="sep">:</td>
                <td>{{ $waktuMulai }} — {{ $waktuSelesai }} WIB</td>
            </tr>
        </table>

        {{-- Attendance Table --}}
        <table class="attendance">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:110px;">NISN</th>
                    <th>Nama Siswa</th>
                    <th>Mata Pelajaran</th>
                    <th style="width:80px;">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $i => $student)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $student->nisn }}</td>
                    <td>{{ $student->nama }}</td>
                    <td>{{ $subjects ?: '-' }}</td>
                    <td class="ttd"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer-section">
            <div class="footer-sign">
                Seputih Raman, {{ $tanggal }}<br>
                Pengawas Ruang<br>
                <div class="ttd-space"></div>
                <div class="dotted">.........................................</div>
                NIP. ................................
            </div>
        </div>
    </div>
</body>
</html>

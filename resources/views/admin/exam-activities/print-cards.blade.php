<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Ujian - {{ $activity->nama_kegiatan }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 10mm; }
        body {
            font-family: 'Times New Roman', serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-header {
            text-align: center;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .print-header h2 { font-size: 22px; color: #1e293b; }
        .print-header p { color: #64748b; font-size: 14px; margin-top: 4px; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; background: #7c3aed; color: white;
            border: none; border-radius: 10px; font-size: 15px;
            font-weight: 600; cursor: pointer; margin-top: 12px;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-print:hover { background: #6d28d9; }

        @media print {
            .print-header { display: none !important; }
        }

        /* Page grid: 2 columns x 5 rows = 10 cards per A4 */
        .page {
            width: 190mm;
            height: 277mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(5, 1fr);
            gap: 3mm;
            page-break-after: always;
            margin: 0 auto;
        }
        .page:last-child { page-break-after: auto; }

        .card {
            border: 1.5px solid #333;
            border-radius: 4px;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 10pt;
            overflow: hidden;
        }

        .card-kop {
            text-align: center;
            border-bottom: 1.5px solid #333;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }
        .card-kop .kop-title {
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-kop .kop-kegiatan {
            font-size: 9pt;
            margin-top: 1mm;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            line-height: 1.6;
        }
        .card-body .kelompok-nomor {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        .card-body .nama-siswa {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }
        .card-body .nisn {
            font-size: 10pt;
            letter-spacing: 1px;
        }

        .card-footer {
            border-top: 1px solid #999;
            padding-top: 2mm;
            margin-top: 2mm;
            font-size: 8pt;
            text-align: right;
            line-height: 1.4;
        }
        .card-footer .ttd-area {
            margin-top: 8mm;
        }
        .card-footer .nama-kepsek {
            font-weight: bold;
            text-decoration: underline;
            font-size: 8.5pt;
        }
        .card-footer .nip {
            font-size: 7.5pt;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h2>🎓 Kartu Ujian Peserta</h2>
        <p>{{ $activity->nama_kegiatan }} — {{ count($cards) }} kartu</p>
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Kartu
        </button>
    </div>

    @php
        $tanggal = \Carbon\Carbon::parse($activity->tanggal_pelaksanaan)->translatedFormat('d F Y');
        $chunks = array_chunk($cards, 10);
    @endphp

    @foreach($chunks as $chunk)
    <div class="page">
        @foreach($chunk as $card)
        <div class="card">
            <div class="card-kop">
                <div class="kop-title">Kartu Ujian</div>
                <div class="kop-kegiatan">{{ $activity->nama_kegiatan }}</div>
            </div>
            <div class="card-body">
                <div class="kelompok-nomor">{{ $card['kelompok'] }} - {{ $card['nomor'] }}</div>
                <div class="nama-siswa">{{ $card['nama'] }}</div>
                <div class="nisn">{{ $card['nisn'] }}</div>
            </div>
            <div class="card-footer">
                Seputih Raman, {{ $tanggal }}<br>
                Kepala Sekolah<br>
                <div class="ttd-area"></div>
                <div class="nama-kepsek">HARYONO, S.Sos, M.Pd</div>
                <div class="nip">NIP. 19770418 200604 1 009</div>
            </div>
        </div>
        @endforeach

        {{-- Fill empty cells if less than 10 cards on last page --}}
        @for($i = count($chunk); $i < 10; $i++)
        <div class="card" style="border:none;"></div>
        @endfor
    </div>
    @endforeach
</body>
</html>

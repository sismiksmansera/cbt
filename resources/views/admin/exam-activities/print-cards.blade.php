<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Ujian - {{ $activity->nama_kegiatan }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 8mm; }
        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: #f1f5f9;
        }

        .print-header {
            text-align: center;
            padding: 24px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            border-radius: 14px;
            margin: 20px auto;
            max-width: 800px;
        }
        .print-header h2 { font-size: 22px; font-weight: 700; }
        .print-header p { opacity: 0.9; font-size: 14px; margin-top: 4px; }
        .btn-print {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; background: rgba(255,255,255,0.2); color: white;
            border: 2px solid rgba(255,255,255,0.4); border-radius: 10px;
            font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 14px;
            font-family: inherit; backdrop-filter: blur(5px);
        }
        .btn-print:hover { background: rgba(255,255,255,0.3); }

        @media print {
            .print-header { display: none !important; }
            body { background: white; }
        }

        /* Page grid: 2 columns x 4 rows = 8 cards per A4 */
        .page {
            width: 194mm;
            height: 277mm;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: repeat(4, 1fr);
            gap: 2mm;
            page-break-after: always;
            margin: 0 auto;
            background: white;
        }
        .page:last-child { page-break-after: auto; }

        .card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3.5mm;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* Accent bar top */
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, #7c3aed, #3b82f6);
        }

        /* KOP */
        .card-kop {
            display: flex;
            align-items: center;
            gap: 2mm;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-kop img {
            width: 8mm;
            height: 8mm;
            object-fit: contain;
        }
        .card-kop .kop-text {
            text-align: center;
            flex: 1;
        }
        .card-kop .kop-title {
            font-weight: 700;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1e293b;
        }
        .card-kop .kop-kegiatan {
            font-size: 7pt;
            color: #64748b;
            margin-top: 0.3mm;
        }

        /* Body = left column (photo+QR) + right column (info) */
        .card-body {
            flex: 1;
            display: flex;
            gap: 3mm;
        }

        .card-left {
            display: flex;
            flex-direction: column;
            gap: 1.5mm;
            flex-shrink: 0;
            width: 20mm;
        }

        /* Photo placeholder */
        .card-photo {
            width: 20mm;
            height: 27mm;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }
        .card-photo .photo-label {
            font-size: 5.5pt;
            color: #94a3b8;
            text-align: center;
            line-height: 1.3;
        }

        .card-qr {
            width: 20mm;
            height: 20mm;
        }
        .card-qr img {
            width: 100%;
            height: 100%;
        }

        .card-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .card-info .label {
            font-size: 5.5pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.3mm;
        }
        .card-info .kelompok-nomor {
            font-size: 8.5pt;
            font-weight: 700;
            color: #7c3aed;
            margin-bottom: 2mm;
        }
        .card-info .nama-siswa {
            font-size: 10pt;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 2mm;
            line-height: 1.2;
        }
        .card-info .nisn {
            font-size: 9pt;
            font-family: 'Consolas', 'Courier New', monospace;
            color: #334155;
            letter-spacing: 0.8px;
            background: #f1f5f9;
            display: inline-block;
            padding: 0.5mm 2mm;
            border-radius: 2px;
        }

        /* Footer */
        .card-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 1mm;
            margin-top: 2mm;
            font-size: 6.5pt;
            text-align: center;
            color: #94a3b8;
            letter-spacing: 0.3px;
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
        $chunks = array_chunk($cards, 8);
    @endphp

    @foreach($chunks as $chunk)
    <div class="page">
        @foreach($chunk as $card)
        <div class="card">
            <div class="card-kop">
                <img src="{{ asset('images/logo-lampung.png') }}" alt="Lampung">
                <div class="kop-text">
                    <div class="kop-title">Kartu Ujian</div>
                    <div class="kop-kegiatan">{{ $activity->nama_kegiatan }}</div>
                </div>
                <img src="{{ asset('images/logo-sekolah.png') }}" alt="Sekolah">
            </div>
            <div class="card-body">
                <div class="card-left">
                    <div class="card-photo">
                        <div class="photo-label">Foto<br>2×2.7</div>
                    </div>
                    <div class="card-qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('https://cbt.smansera.app/login?nisn=' . $card['nisn']) }}" alt="QR">
                    </div>
                </div>
                <div class="card-info">
                    <div class="label">Kelompok Tes</div>
                    <div class="kelompok-nomor">{{ $card['kelompok'] }} - {{ $card['nomor'] }}</div>
                    <div class="label">Nama Peserta</div>
                    <div class="nama-siswa">{{ $card['nama'] }}</div>
                    <div class="label">NISN</div>
                    <div class="nisn">{{ $card['nisn'] }}</div>
                </div>
            </div>
            <div class="card-footer">
                https://cbt.smansera.app/
            </div>
        </div>
        @endforeach

        @for($i = count($chunk); $i < 8; $i++)
        <div class="card" style="border:none;"></div>
        @endfor
    </div>
    @endforeach
</body>
</html>

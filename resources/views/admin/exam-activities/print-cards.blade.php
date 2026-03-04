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
            font-family: inherit;
        }
        .btn-print:hover { background: rgba(255,255,255,0.3); }

        @media print {
            .print-header { display: none !important; }
            body { background: white; }
        }

        /* Page: 2 columns x 4 rows = 8 cards */
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

        /* === CARD === */
        .card {
            border: 1.5px solid #1e293b;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Header band */
        .card-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            display: flex;
            align-items: center;
            padding: 2mm 3mm;
            gap: 2mm;
        }
        .card-header img {
            width: 7mm;
            height: 7mm;
            object-fit: contain;
            border-radius: 1px;
        }
        .card-header .header-text {
            flex: 1;
            text-align: center;
        }
        .card-header .header-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .card-header .header-sub {
            font-size: 6pt;
            opacity: 0.85;
            margin-top: 0.3mm;
        }


        /* Body */
        .card-body {
            flex: 1;
            display: flex;
            padding: 3mm;
            gap: 3mm;
        }

        /* Photo */
        .card-photo {
            width: 20mm;
            height: 27mm;
            border: 1px solid #94a3b8;
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            flex-shrink: 0;
        }
        .card-photo span {
            font-size: 5pt;
            color: #94a3b8;
            text-align: center;
            line-height: 1.4;
        }

        /* Right: Info + QR */
        .card-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-info {
            display: flex;
            flex-direction: column;
            gap: 1mm;
        }
        .info-row {
            display: flex;
            flex-direction: column;
        }
        .info-row .lbl {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
        }
        .info-row .val {
            font-size: 9pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0.3mm;
        }
        .info-row .val.kelompok {
            color: #7c3aed;
            font-size: 9pt;
        }
        .info-row .val.nama {
            font-size: 10.5pt;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .info-row .val.nisn {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 9pt;
            letter-spacing: 1px;
            color: #334155;
        }

        /* QR bottom-right */
        .card-qr-row {
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            margin-top: 1mm;
        }
        .card-qr {
            width: 16mm;
            height: 16mm;
        }
        .card-qr img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Footer */
        .card-footer {
            background: #f1f5f9;
            text-align: center;
            padding: 1mm;
            font-size: 6pt;
            color: #64748b;
            letter-spacing: 0.5px;
            border-top: 1px solid #e2e8f0;
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
            <div class="card-header">
                <img src="{{ asset('images/logo-lampung.png') }}" alt="Lampung">
                <div class="header-text">
                    <div class="header-title">Kartu Ujian</div>
                    <div class="header-sub">{{ $activity->nama_kegiatan }}</div>
                </div>
                <img src="{{ asset('images/logo-sekolah.png') }}" alt="Sekolah">
            </div>
            <div class="card-body">
                <div class="card-photo">
                    <span>Pas Foto<br>2 × 2.7 cm</span>
                </div>
                <div class="card-right">
                    <div class="card-info">
                        <div class="info-row">
                            <span class="lbl">Kelompok Tes / No. Urut</span>
                            <span class="val kelompok">{{ $card['kelompok'] }} — {{ $card['nomor'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="lbl">Nama Peserta</span>
                            <span class="val nama">{{ $card['nama'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="lbl">NISN</span>
                            <span class="val nisn">{{ $card['nisn'] }}</span>
                        </div>
                    </div>
                    <div class="card-qr-row">
                        <div class="card-qr">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('https://cbt.smansera.app/login?nisn=' . $card['nisn']) }}" alt="QR">
                        </div>
                    </div>
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

@extends('layouts.admin')
@section('title', 'Soal: ' . $exam->kategori)

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h3 style="font-size:18px;">{{ $exam->kategori }}</h3>
        <p style="color:var(--text-secondary);font-size:13px;">{{ $exam->subject->nama ?? '-' }} • {{ $exam->questions->count() }} soal</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-outline btn-sm" onclick="document.getElementById('importModal').style.display='flex'"><i class="fas fa-file-import"></i> Import Soal</button>
        <button class="btn btn-outline btn-sm" style="border-color:rgba(245,158,11,0.4);color:#f59e0b;" onclick="document.getElementById('importKeyModal').style.display='flex'"><i class="fas fa-key"></i> Import Kunci</button>
        <a href="{{ route('admin.questions.create', ['exam_id' => $exam->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Soal</a>
    </div>
</div>

@if(session('success'))
<div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;margin-bottom:16px;font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@forelse($exam->questions as $i => $q)
<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:32px;height:32px;border-radius:8px;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">{{ $i + 1 }}</div>
            <div>
                <span class="badge badge-info" style="font-size:11px;">{{ str_replace('_', ' ', ucfirst($q->tipe)) }}</span>
                <span class="badge badge-purple" style="font-size:11px;">Bobot: {{ $q->bobot }}</span>
            </div>
        </div>
        <div style="display:flex;gap:4px;">
            <a href="{{ route('admin.questions.edit', $q->id) }}" class="btn btn-outline btn-icon btn-sm"><i class="fas fa-pen"></i></a>
            <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">@csrf @method('DELETE')
                <button class="btn btn-danger btn-icon btn-sm"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>
    <div class="card-body-padded">
        <div style="margin-bottom:12px;">{!! nl2br($q->pertanyaan) !!}</div>
        <style>.card-body-padded img { max-width:100%; height:auto; border-radius:8px; margin:8px 0; display:block; }</style>
        @if(in_array($q->tipe, ['multiple_choice', 'multiple_answer', 'true_false']))
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach($q->options as $opt)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 14px;border-radius:8px;background:{{ $opt->is_correct ? 'rgba(16,185,129,0.1)' : 'rgba(15,23,42,0.5)' }};border:1px solid {{ $opt->is_correct ? 'rgba(16,185,129,0.3)' : 'var(--border-color)' }};">
                    @if($opt->is_correct)<i class="fas fa-check-circle" style="color:var(--success);"></i>@else<i class="far fa-circle" style="color:var(--text-secondary);"></i>@endif
                    <span>{!! $opt->teks_opsi !!}</span>
                </div>
                @endforeach
            </div>
        @elseif($q->tipe === 'matching')
            <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:8px;align-items:center;">
                @foreach($q->options as $opt)
                    <div style="padding:8px 14px;border-radius:8px;background:rgba(15,23,42,0.5);border:1px solid var(--border-color);">{{ $opt->teks_opsi }}</div>
                    <i class="fas fa-arrows-alt-h" style="color:var(--text-secondary);"></i>
                    <div style="padding:8px 14px;border-radius:8px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);">{{ $opt->teks_pasangan }}</div>
                @endforeach
            </div>
        @elseif($q->tipe === 'short_answer')
            @php $ans = $q->options->first(); @endphp
            @if($ans)<div style="padding:8px 14px;border-radius:8px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);"><i class="fas fa-check" style="color:var(--success);margin-right:8px;"></i>{{ $ans->teks_opsi }}</div>@endif
        @elseif($q->tipe === 'essay')
            <div style="color:var(--text-secondary);font-style:italic;font-size:13px;"><i class="fas fa-pen-fancy"></i> Jawaban essay (dinilai manual)</div>
        @endif
    </div>
</div>
@empty
<div class="card"><div class="empty-state" style="padding:40px;"><i class="fas fa-question-circle"></i><p>Belum ada soal untuk kategori ini</p><a href="{{ route('admin.questions.create', ['exam_id' => $exam->id]) }}" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-plus"></i> Tambah Soal Pertama</a></div></div>
@endforelse

<!-- Import Modal -->
<div id="importModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:20px;padding:32px;max-width:600px;width:90%;border:1px solid var(--border-color);box-shadow:0 25px 60px rgba(0,0,0,0.5);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-size:18px;"><i class="fas fa-file-import" style="color:var(--primary);margin-right:8px;"></i>Import Soal</h3>
            <button onclick="document.getElementById('importModal').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>

        <form method="POST" action="{{ route('admin.questions.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="form-group">
                <label>Format File <span style="color:var(--danger);">*</span></label>
                <select name="format" class="form-control" required id="importFormat" onchange="updateFormatInfo()">
                    <option value="word_table">Word Tabel (.docx)</option>
                    <option value="word_text">Word Teks (.docx)</option>
                    <option value="gift">GIFT Format (Moodle)</option>
                    <option value="blackboard">Blackboard Format</option>
                </select>
            </div>

            <div class="form-group">
                <label>File Soal <span style="color:var(--danger);">*</span></label>
                <input type="file" name="file" class="form-control" accept=".txt,.gift,.docx" required>
            </div>

            <div id="formatInfo" style="padding:14px;border-radius:10px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.12);margin-bottom:16px;font-size:12px;color:#60a5fa;">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('importModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Answer Key Modal -->
<div id="importKeyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);z-index:200;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:20px;padding:32px;max-width:520px;width:90%;border:1px solid var(--border-color);box-shadow:0 25px 60px rgba(0,0,0,0.5);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-size:18px;"><i class="fas fa-key" style="color:#f59e0b;margin-right:8px;"></i>Import Kunci Jawaban</h3>
            <button onclick="document.getElementById('importKeyModal').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>

        <form method="POST" action="{{ route('admin.questions.import-answer-key') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">

            <div class="form-group">
                <label>File Excel <span style="color:var(--danger);">*</span></label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>

            <div style="padding:14px;border-radius:10px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.12);margin-bottom:16px;font-size:12px;color:#fbbf24;">
                <strong><i class="fas fa-info-circle"></i> Format Excel</strong><br><br>
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(245,158,11,0.2);">
                            <th style="text-align:left;padding:4px 8px;">Kolom A</th>
                            <th style="text-align:left;padding:4px 8px;">Kolom B</th>
                            <th style="text-align:left;padding:4px 8px;">Kolom C</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid rgba(245,158,11,0.1);">
                            <td style="padding:4px 8px;">No</td>
                            <td style="padding:4px 8px;">Kunci Jawaban</td>
                            <td style="padding:4px 8px;">Bobot</td>
                        </tr>
                        <tr style="border-bottom:1px solid rgba(245,158,11,0.1);">
                            <td style="padding:4px 8px;">1</td>
                            <td style="padding:4px 8px;">A</td>
                            <td style="padding:4px 8px;">1</td>
                        </tr>
                        <tr style="border-bottom:1px solid rgba(245,158,11,0.1);">
                            <td style="padding:4px 8px;">2</td>
                            <td style="padding:4px 8px;">C</td>
                            <td style="padding:4px 8px;">2</td>
                        </tr>
                        <tr>
                            <td style="padding:4px 8px;">3</td>
                            <td style="padding:4px 8px;">B</td>
                            <td style="padding:4px 8px;">1</td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <small>Kunci: A-E. Bobot opsional (default tetap).</small>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('importKeyModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-upload"></i> Import Kunci</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateFormatInfo() {
    const format = document.getElementById('importFormat').value;
    const info = document.getElementById('formatInfo');
    if (format === 'word_table') {
        info.innerHTML = `<strong><i class="fas fa-info-circle"></i> Format Word Tabel (.docx)</strong><br><br>
<strong>Struktur Tabel:</strong><br>
Setiap soal terdiri dari baris soal + baris-baris jawaban.<br><br>
<strong>Baris Soal:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">Kolom 1: Nomor soal (1.) | Kolom 2: Teks pertanyaan</code><br><br>
<strong>Baris Jawaban:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">Kolom 1: Opsi (A/B/C/D/E) | Kolom 2: Teks jawaban</code><br><br>
<strong>Kunci Jawaban:</strong> Tambahkan <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">=</code> di awal opsi untuk menandai jawaban benar, cth: <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">=A</code><br>
Jika tidak ada tanda <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">=</code>, kunci jawaban bisa diimport terpisah.`;
    } else if (format === 'word_text') {
        info.innerHTML = `<strong><i class="fas fa-info-circle"></i> Format Word Teks (.docx)</strong><br><br>
<strong>Soal:</strong> Diawali penomoran angka: <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">1.</code>, <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">2.</code>, dst.<br>
Seluruh teks dan gambar setelah nomor sampai sebelum huruf opsi menjadi soal.<br><br>
<strong>Jawaban:</strong> Diawali huruf: <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">A.</code>, <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">B.</code>, dst.<br>
Seluruh teks dan gambar setelah huruf opsi sampai opsi berikutnya atau soal berikutnya menjadi jawaban.<br><br>
<strong>Contoh:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">1. Teks pertanyaan di sini...<br>&nbsp;&nbsp;&nbsp;A. Jawaban opsi A<br>&nbsp;&nbsp;&nbsp;B. Jawaban opsi B<br>&nbsp;&nbsp;&nbsp;C. Jawaban opsi C<br>2. Pertanyaan berikutnya...</code>`;
    } else if (format === 'gift') {
        info.innerHTML = `<strong><i class="fas fa-info-circle"></i> GIFT Format (Moodle)</strong><br><br>
<strong>Pilihan Ganda:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">::Judul:: Pertanyaan {=Jawaban benar ~Jawaban salah 1 ~Jawaban salah 2}</code><br><br>
<strong>Multi Jawaban (Penskoran %):</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">::1:: Pertanyaan {~%50% Benar 1 ~%-33.33% Salah 1 ~%50% Benar 2 ~%-33.33% Salah 2}</code><br><br>
<strong>Benar/Salah:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">Pernyataan {T}</code> atau <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">{F}</code><br><br>
<strong>Jawaban Singkat:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">Pertanyaan {=jawaban}</code>`;
    } else {
        info.innerHTML = `<strong><i class="fas fa-info-circle"></i> Blackboard Format (Tab-separated)</strong><br><br>
<strong>Pilihan Ganda:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">MC&#9;Pertanyaan&#9;Opsi A&#9;correct&#9;Opsi B&#9;incorrect&#9;...</code><br><br>
<strong>Benar/Salah:</strong><br>
<code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">TF&#9;Pernyataan&#9;true</code><br><br>
<strong>Essay:</strong> <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">ESS&#9;Pertanyaan</code><br>
<strong>Jawaban Singkat:</strong> <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:11px;">SA&#9;Pertanyaan&#9;Jawaban</code>`;
    }
}
updateFormatInfo();
</script>
@endsection

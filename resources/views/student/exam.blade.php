<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian: {{ $session->nama_sesi }} | CBT</title>
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
    <style>
        :root {
            --bg: #0f172a; --bg-card: #1e293b; --bg-sidebar: #1e293b;
            --primary: #3b82f6; --primary-dark: #2563eb;
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
            --text: #f1f5f9; --text-sec: #94a3b8; --border: #334155;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* Top bar */
        .exam-topbar {
            position: fixed; top: 0; left: 0; right: 0; height: 64px;
            background: var(--bg-card); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; z-index: 100;
        }
        .exam-info h3 { font-size: 16px; font-weight: 700; }
        .exam-info p { font-size: 12px; color: var(--text-sec); }
        .timer {
            display: flex; align-items: center; gap: 10px;
            background: rgba(239,68,68,0.12); padding: 10px 20px;
            border-radius: 12px; border: 1px solid rgba(239,68,68,0.2);
        }
        .timer.warning { background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.2); }
        .timer.danger { animation: pulse 1s infinite; }
        @keyframes pulse { 50% { opacity: 0.7; } }
        .timer-icon { font-size: 20px; color: var(--danger); }
        .timer-text { font-size: 24px; font-weight: 800; font-variant-numeric: tabular-nums; }

        /* Layout */
        .exam-layout { display: flex; margin-top: 64px; min-height: calc(100vh - 64px); }
        .exam-main { flex: 1; padding: 24px; overflow-y: auto; }
        .exam-sidebar {
            width: 280px; background: var(--bg-sidebar);
            border-left: 1px solid var(--border); padding: 20px;
            display: flex; flex-direction: column; position: sticky;
            top: 64px; height: calc(100vh - 64px); overflow-y: auto;
        }

        /* Question */
        .question-card {
            background: var(--bg-card); border-radius: 16px;
            border: 1px solid var(--border); padding: 28px;
            display: none; animation: fadeIn 0.3s ease;
        }
        .question-card.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .question-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
        }
        .question-number {
            display: flex; align-items: center; gap: 12px;
        }
        .q-num {
            width: 40px; height: 40px; border-radius: 10px;
            background: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 16px;
        }
        .q-type { font-size: 12px; color: var(--text-sec); }
        .q-type span { background: rgba(59,130,246,0.15); color: var(--primary); padding: 3px 10px; border-radius: 6px; font-weight: 600; }
        .flag-btn {
            background: none; border: 1px solid var(--border);
            color: var(--text-sec); padding: 8px 14px; border-radius: 8px;
            cursor: pointer; font-size: 14px; transition: all 0.2s;
        }
        .flag-btn.flagged { background: rgba(245,158,11,0.15); border-color: var(--warning); color: var(--warning); }
        .question-text { font-size: 16px; line-height: 1.7; margin-bottom: 24px; }

        /* Options */
        .option-item {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 18px; border-radius: 12px;
            border: 2px solid var(--border); margin-bottom: 10px;
            cursor: pointer; transition: all 0.2s;
        }
        .option-item:hover { border-color: var(--primary); background: rgba(59,130,246,0.05); }
        .option-item.selected { border-color: var(--primary); background: rgba(59,130,246,0.1); }
        .option-radio {
            width: 22px; height: 22px; border-radius: 50%;
            border: 2px solid var(--border); display: flex;
            align-items: center; justify-content: center; flex-shrink: 0;
            transition: all 0.2s;
        }
        .option-item.selected .option-radio {
            border-color: var(--primary); background: var(--primary);
        }
        .option-item.selected .option-radio::after {
            content: ''; width: 8px; height: 8px; border-radius: 50%; background: white;
        }
        .option-check {
            width: 22px; height: 22px; border-radius: 6px;
            border: 2px solid var(--border); display: flex;
            align-items: center; justify-content: center; flex-shrink: 0;
            transition: all 0.2s; font-size: 12px; color: transparent;
        }
        .option-item.selected .option-check {
            border-color: var(--primary); background: var(--primary); color: white;
        }
        .option-label { font-weight: 600; color: var(--primary); min-width: 28px; }
        .option-text { font-size: 14px; }

        /* Short answer / Essay */
        .answer-input {
            width: 100%; padding: 14px 18px; border-radius: 12px;
            border: 2px solid var(--border); background: rgba(15,23,42,0.5);
            color: var(--text); font-size: 15px; font-family: 'Inter';
            transition: border-color 0.2s;
        }
        .answer-input:focus { outline: none; border-color: var(--primary); }
        textarea.answer-input { min-height: 150px; resize: vertical; }

        /* Navigation */
        .nav-buttons {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border);
        }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: 'Inter';
            border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .btn-prev { background: rgba(99,102,241,0.1); color: #818cf8; }
        .btn-prev:hover { background: rgba(99,102,241,0.2); }
        .btn-next { background: var(--primary); color: white; }
        .btn-next:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-submit { background: var(--success); color: white; font-size: 15px; padding: 14px 28px; }
        .btn-submit:hover { background: #059669; }

        /* Sidebar - Question Map */
        .sidebar-title { font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text); }
        .student-info {
            padding: 14px; border-radius: 12px; background: rgba(15,23,42,0.5);
            border: 1px solid var(--border); margin-bottom: 20px;
        }
        .student-info p { font-size: 12px; color: var(--text-sec); }
        .student-info strong { color: var(--text); font-size: 14px; }
        .question-map {
            display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 20px;
        }
        .q-map-btn {
            width: 100%; aspect-ratio: 1; border-radius: 8px;
            border: 1px solid var(--border); background: rgba(15,23,42,0.5);
            color: var(--text-sec); font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .q-map-btn.active { border-color: var(--primary); background: var(--primary); color: white; }
        .q-map-btn.answered { background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3); color: var(--success); }
        .q-map-btn.flagged { background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.3); color: var(--warning); }

        .map-legend {
            display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
            padding: 12px; border-radius: 10px; background: rgba(15,23,42,0.5);
        }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text-sec); }
        .legend-dot { width: 12px; height: 12px; border-radius: 4px; }

        /* Matching */
        .match-grid { display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center; }
        .match-left {
            padding: 12px 16px; border-radius: 10px;
            background: rgba(15,23,42,0.5); border: 1px solid var(--border);
            font-size: 14px;
        }
        .match-select {
            width: 100%; padding: 12px 16px; border-radius: 10px;
            border: 1px solid var(--border); background: rgba(15,23,42,0.5);
            color: var(--text); font-size: 14px; font-family: 'Inter';
        }
        .match-select:focus { outline: none; border-color: var(--primary); }

        /* Modals */
        .submit-modal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(8px);
            z-index: 200; align-items: center; justify-content: center;
        }
        .submit-modal.active { display: flex; }
        .submit-dialog {
            background: var(--bg-card); border-radius: 20px;
            padding: 36px; max-width: 440px; width: 90%;
            border: 1px solid var(--border); text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .submit-dialog i { font-size: 48px; color: var(--warning); margin-bottom: 16px; }
        .submit-dialog h3 { font-size: 20px; margin-bottom: 8px; }
        .submit-dialog p { color: var(--text-sec); font-size: 14px; margin-bottom: 24px; }
        .submit-stats { display: flex; justify-content: center; gap: 24px; margin-bottom: 24px; }
        .submit-stat { text-align: center; }
        .submit-stat-value { font-size: 28px; font-weight: 800; }
        .submit-stat-label { font-size: 11px; color: var(--text-sec); }
        .submit-actions { display: flex; gap: 12px; justify-content: center; }
        .btn-cancel { background: rgba(239,68,68,0.1); color: var(--danger); }
        .btn-confirm { background: var(--success); color: white; }

        /* Responsive */
        @media (max-width: 768px) {
            .exam-sidebar { display: none; position: fixed; top: 64px; right: 0; bottom: 0; z-index: 90; width: 280px; }
            .exam-sidebar.open { display: flex; }
            .toggle-sidebar { display: block; }
            .exam-main { padding: 16px; }
            .timer-text { font-size: 18px; }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="exam-topbar">
        <div class="exam-info">
            <h3>{{ $session->nama_sesi }}</h3>
            <p>{{ $questions->count() }} soal</p>
        </div>
        <div class="timer" id="timerBox">
            <i class="fas fa-clock timer-icon"></i>
            <span class="timer-text" id="timerDisplay">--:--</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="flag-btn" onclick="document.querySelector('.exam-sidebar').classList.toggle('open')" style="display:none;" id="sidebarToggle">
                <i class="fas fa-th"></i>
            </button>
            <div style="font-size:13px;color:var(--text-sec);">
                <i class="fas fa-user"></i> {{ session('student_name') }}
            </div>
        </div>
    </div>

    <div class="exam-layout">
        <!-- Main Content -->
        <div class="exam-main">
            @foreach($questions as $idx => $q)
            <div class="question-card {{ $idx === 0 ? 'active' : '' }}" data-index="{{ $idx }}" data-id="{{ $q->id }}" data-type="{{ $q->tipe }}">
                <div class="question-header">
                    <div class="question-number">
                        <div class="q-num">{{ $idx + 1 }}</div>
                        <div class="q-type">
                            <span>
                                @switch($q->tipe)
                                    @case('multiple_choice') Pilihan Ganda @break
                                    @case('multiple_answer') Jawaban Ganda @break
                                    @case('true_false') Benar / Salah @break
                                    @case('matching') Menjodohkan @break
                                    @case('short_answer') Jawaban Singkat @break
                                    @case('essay') Essay @break
                                @endswitch
                            </span>
                        </div>
                    </div>
                    <button class="flag-btn {{ isset($answers[$q->id]) && $answers[$q->id]->is_flagged ? 'flagged' : '' }}"
                            onclick="toggleFlag({{ $q->id }}, this)">
                        <i class="fas fa-flag"></i> Tandai
                    </button>
                </div>

                <div class="question-text">{!! nl2br($q->pertanyaan) !!}</div>

                @if($q->tipe === 'multiple_choice' || $q->tipe === 'true_false')
                    @php $saved = $answers[$q->id]->jawaban ?? null; @endphp
                    @foreach($q->options as $oi => $opt)
                    <div class="option-item {{ $saved == $opt->id ? 'selected' : '' }}"
                         onclick="selectOption(this, {{ $q->id }}, {{ $opt->id }})">
                        <div class="option-radio"></div>
                        <span class="option-label">{{ chr(65 + $oi) }}.</span>
                        <span class="option-text">{!! $opt->teks_opsi !!}</span>
                    </div>
                    @endforeach

                @elseif($q->tipe === 'multiple_answer')
                    @php $savedArr = json_decode($answers[$q->id]->jawaban ?? '[]', true) ?: []; @endphp
                    @foreach($q->options as $oi => $opt)
                    <div class="option-item {{ in_array($opt->id, $savedArr) ? 'selected' : '' }}"
                         onclick="toggleMultiOption(this, {{ $q->id }}, {{ $opt->id }})">
                        <div class="option-check"><i class="fas fa-check"></i></div>
                        <span class="option-label">{{ chr(65 + $oi) }}.</span>
                        <span class="option-text">{!! $opt->teks_opsi !!}</span>
                    </div>
                    @endforeach

                @elseif($q->tipe === 'matching')
                    @php
                        $savedPairs = json_decode($answers[$q->id]->jawaban ?? '{}', true) ?: [];
                        $shuffledRight = $q->options->pluck('teks_pasangan')->shuffle();
                    @endphp
                    <div class="match-grid">
                        @foreach($q->options as $opt)
                            <div class="match-left">{{ $opt->teks_opsi }}</div>
                            <i class="fas fa-arrow-right" style="color:var(--text-sec);"></i>
                            <select class="match-select" onchange="saveMatching({{ $q->id }}, {{ $opt->id }}, this.value)">
                                <option value="">-- Pilih --</option>
                                @foreach($shuffledRight as $rText)
                                    <option value="{{ $rText }}" {{ ($savedPairs[$opt->id] ?? '') == $rText ? 'selected' : '' }}>{{ $rText }}</option>
                                @endforeach
                            </select>
                        @endforeach
                    </div>

                @elseif($q->tipe === 'short_answer')
                    <input type="text" class="answer-input" placeholder="Ketik jawaban Anda..."
                           value="{{ $answers[$q->id]->jawaban ?? '' }}"
                           onchange="saveTextAnswer({{ $q->id }}, this.value)">

                @elseif($q->tipe === 'essay')
                    <textarea class="answer-input" placeholder="Tulis jawaban essay Anda..."
                              onchange="saveTextAnswer({{ $q->id }}, this.value)">{{ $answers[$q->id]->jawaban ?? '' }}</textarea>
                @endif

                <div class="nav-buttons">
                    <button class="btn btn-prev" onclick="goToQuestion({{ $idx - 1 }})" {{ $idx === 0 ? 'disabled style=opacity:0.3;pointer-events:none;' : '' }}>
                        <i class="fas fa-arrow-left"></i> Sebelumnya
                    </button>
                    @if($idx < $questions->count() - 1)
                        <button class="btn btn-next" onclick="goToQuestion({{ $idx + 1 }})">
                            Selanjutnya <i class="fas fa-arrow-right"></i>
                        </button>
                    @else
                        <button class="btn btn-submit" onclick="showSubmitModal()">
                            <i class="fas fa-paper-plane"></i> Selesai & Kumpulkan
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Sidebar -->
        <div class="exam-sidebar">
            <div class="student-info">
                <p>Peserta Ujian</p>
                <strong>{{ session('student_name') }}</strong>
                <p style="margin-top:4px;">{{ session('student_nisn') }}</p>
            </div>

            <div class="sidebar-title">Navigasi Soal</div>
            <div class="question-map" id="questionMap">
                @foreach($questions as $idx => $q)
                <button class="q-map-btn {{ $idx === 0 ? 'active' : '' }}
                    {{ isset($answers[$q->id]) && !empty($answers[$q->id]->jawaban) ? 'answered' : '' }}
                    {{ isset($answers[$q->id]) && $answers[$q->id]->is_flagged ? 'flagged' : '' }}"
                    onclick="goToQuestion({{ $idx }})" data-qid="{{ $q->id }}">
                    {{ $idx + 1 }}
                </button>
                @endforeach
            </div>

            <div class="map-legend">
                <div class="legend-item"><div class="legend-dot" style="background:var(--primary);"></div> Aktif</div>
                <div class="legend-item"><div class="legend-dot" style="background:rgba(16,185,129,0.3);border:1px solid var(--success);"></div> Dijawab</div>
                <div class="legend-item"><div class="legend-dot" style="background:rgba(245,158,11,0.3);border:1px solid var(--warning);"></div> Ditandai</div>
                <div class="legend-item"><div class="legend-dot" style="background:rgba(15,23,42,0.5);border:1px solid var(--border);"></div> Belum</div>
            </div>

            <div id="answerStats" style="padding:12px;border-radius:10px;background:rgba(15,23,42,0.5);border:1px solid var(--border);margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                    <span style="color:var(--text-sec);">Dijawab</span>
                    <span id="answeredCount" style="font-weight:700;color:var(--success);">0</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                    <span style="color:var(--text-sec);">Belum</span>
                    <span id="unansweredCount" style="font-weight:700;color:var(--danger);">{{ $questions->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-sec);">Ditandai</span>
                    <span id="flaggedCount" style="font-weight:700;color:var(--warning);">0</span>
                </div>
            </div>

            <button class="btn btn-submit" style="width:100%;justify-content:center;" onclick="showSubmitModal()">
                <i class="fas fa-paper-plane"></i> Kumpulkan Jawaban
            </button>
        </div>
    </div>

    <!-- Submit Modal -->
    <div class="submit-modal" id="submitModal">
        <div class="submit-dialog">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Kumpulkan Jawaban?</h3>
            <p>Pastikan semua soal sudah dijawab. Jawaban tidak dapat diubah setelah dikumpulkan.</p>
            <div class="submit-stats">
                <div class="submit-stat">
                    <div class="submit-stat-value" id="modalAnswered" style="color:var(--success);">0</div>
                    <div class="submit-stat-label">Dijawab</div>
                </div>
                <div class="submit-stat">
                    <div class="submit-stat-value" id="modalUnanswered" style="color:var(--danger);">0</div>
                    <div class="submit-stat-label">Belum Dijawab</div>
                </div>
                <div class="submit-stat">
                    <div class="submit-stat-value" id="modalFlagged" style="color:var(--warning);">0</div>
                    <div class="submit-stat-label">Ditandai</div>
                </div>
            </div>
            <div class="submit-actions">
                <button class="btn btn-cancel" onclick="document.getElementById('submitModal').classList.remove('active')">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button class="btn btn-confirm" onclick="submitExam()">
                    <i class="fas fa-check"></i> Ya, Kumpulkan
                </button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let currentQuestion = 0;
        const totalQuestions = {{ $questions->count() }};
        let remaining = {{ $remaining }};
        const answeredSet = new Set();
        const flaggedSet = new Set();

        // Initialize answered/flagged sets
        @foreach($questions as $q)
            @if(isset($answers[$q->id]) && !empty($answers[$q->id]->jawaban))
                answeredSet.add({{ $q->id }});
            @endif
            @if(isset($answers[$q->id]) && $answers[$q->id]->is_flagged)
                flaggedSet.add({{ $q->id }});
            @endif
        @endforeach

        // Timer
        function updateTimer() {
            if (remaining <= 0) {
                document.getElementById('timerDisplay').textContent = '00:00';
                submitExam();
                return;
            }
            remaining--;
            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            const display = h > 0
                ? `${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
                : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            document.getElementById('timerDisplay').textContent = display;

            const box = document.getElementById('timerBox');
            if (remaining < 60) box.classList.add('danger');
            else if (remaining < 300) box.classList.add('warning');
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // Navigation
        function goToQuestion(idx) {
            if (idx < 0 || idx >= totalQuestions) return;
            document.querySelectorAll('.question-card').forEach(c => c.classList.remove('active'));
            const card = document.querySelectorAll('.question-card')[idx];
            card.classList.add('active');
            renderAllMath(card);
            document.querySelectorAll('.q-map-btn').forEach((b, i) => {
                b.classList.toggle('active', i === idx);
            });
            currentQuestion = idx;
            window.scrollTo(0, 0);
        }

        function updateStats() {
            document.getElementById('answeredCount').textContent = answeredSet.size;
            document.getElementById('unansweredCount').textContent = totalQuestions - answeredSet.size;
            document.getElementById('flaggedCount').textContent = flaggedSet.size;
        }

        function markAnswered(qid) {
            answeredSet.add(qid);
            const btn = document.querySelector(`.q-map-btn[data-qid="${qid}"]`);
            if (btn) btn.classList.add('answered');
            updateStats();
        }

        // Save answer via AJAX
        function saveAnswer(questionId, jawaban) {
            fetch('{{ route("student.exam.save-answer") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ question_id: questionId, jawaban: jawaban })
            });
            markAnswered(questionId);
        }

        // Single choice (MC, T/F)
        function selectOption(el, qid, optId) {
            el.closest('.question-card').querySelectorAll('.option-item').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            saveAnswer(qid, optId);
        }

        // Multiple answer
        function toggleMultiOption(el, qid, optId) {
            el.classList.toggle('selected');
            const selected = [];
            el.closest('.question-card').querySelectorAll('.option-item.selected').forEach(o => {
                const onclick = o.getAttribute('onclick');
                const match = onclick.match(/toggleMultiOption\(this,\s*\d+,\s*(\d+)\)/);
                if (match) selected.push(parseInt(match[1]));
            });
            saveAnswer(qid, JSON.stringify(selected));
        }

        // Matching
        function saveMatching(qid, optId, value) {
            const card = document.querySelector(`.question-card[data-id="${qid}"]`);
            const pairs = {};
            card.querySelectorAll('.match-select').forEach(sel => {
                const onchange = sel.getAttribute('onchange');
                const match = onchange.match(/saveMatching\(\d+,\s*(\d+)/);
                if (match && sel.value) pairs[match[1]] = sel.value;
            });
            saveAnswer(qid, JSON.stringify(pairs));
        }

        // Text answers
        let textTimeout;
        function saveTextAnswer(qid, value) {
            clearTimeout(textTimeout);
            textTimeout = setTimeout(() => saveAnswer(qid, value), 500);
        }

        // Flag toggle
        function toggleFlag(qid, btn) {
            fetch('{{ route("student.exam.toggle-flag") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ question_id: qid })
            }).then(r => r.json()).then(data => {
                btn.classList.toggle('flagged', data.flagged);
                const mapBtn = document.querySelector(`.q-map-btn[data-qid="${qid}"]`);
                if (mapBtn) mapBtn.classList.toggle('flagged', data.flagged);
                if (data.flagged) flaggedSet.add(qid); else flaggedSet.delete(qid);
                updateStats();
            });
        }

        // Submit
        function showSubmitModal() {
            document.getElementById('modalAnswered').textContent = answeredSet.size;
            document.getElementById('modalUnanswered').textContent = totalQuestions - answeredSet.size;
            document.getElementById('modalFlagged').textContent = flaggedSet.size;
            document.getElementById('submitModal').classList.add('active');
        }

        function submitExam() {
            fetch('{{ route("student.exam.submit") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        updateStats();

        // Show sidebar toggle on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('sidebarToggle').style.display = 'block';
        }

        // ===== ANTI-CHEAT SECURITY =====
        let examLocked = false;
        const initialWidth = window.innerWidth;
        const initialHeight = window.innerHeight;

        function lockExam() {
            if (examLocked) return;
            examLocked = true;

            // Show lock overlay
            document.getElementById('lockOverlay').style.display = 'flex';

            // Send lock request to server
            fetch('{{ route("student.exam.lock") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            }).then(() => {
                setTimeout(() => {
                    window.location.href = '{{ route("student.login") }}';
                }, 3000);
            });
        }

        // 1. Tab switch / minimize detection (works on both desktop & mobile)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                lockExam();
            }
        });

        // 2. Window blur — detects losing focus (desktop: clicking outside browser, alt-tab)
        window.addEventListener('blur', function() {
            lockExam();
        });

        // 3. Split-screen / multi-window detection — screen resize beyond threshold
        let resizeTimer = null;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const widthDiff = Math.abs(window.innerWidth - initialWidth);
                const heightDiff = Math.abs(window.innerHeight - initialHeight);
                // Significant resize = likely split-screen (more than 15% change)
                if (widthDiff > initialWidth * 0.15 || heightDiff > initialHeight * 0.15) {
                    lockExam();
                }
            }, 300);
        });

        // 4. Detect Picture-in-Picture or page hide
        window.addEventListener('pagehide', function() {
            lockExam();
        });

        // 5. Prevent right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // 6. Prevent keyboard shortcuts (copy, inspect, etc.)
        document.addEventListener('keydown', function(e) {
            if (
                (e.ctrlKey && ['c','v','a','u','s','p'].includes(e.key.toLowerCase())) ||
                e.key === 'F12' ||
                (e.ctrlKey && e.shiftKey && e.key === 'I')
            ) {
                e.preventDefault();
            }
        });

        // 7. Prevent text selection and drag
        document.addEventListener('selectstart', function(e) { e.preventDefault(); });
        document.addEventListener('dragstart', function(e) { e.preventDefault(); });

        // ===== FULLSCREEN LOCK =====
        const isMobile = /Android|iPhone|iPad|iPod|Mobile/i.test(navigator.userAgent);

        function requestFullscreen() {
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
            if (rfs) {
                rfs.call(el).catch(() => {});
            }
        }

        // Request Wake Lock to prevent screen from turning off
        let wakeLock = null;
        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            } catch(e) {}
        }

        // Auto-enter fullscreen on page load (user already confirmed on previous page)
        requestFullscreen();
        requestWakeLock();

        // Detect exit from fullscreen = lock
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                lockExam();
            }
        });
        document.addEventListener('webkitfullscreenchange', function() {
            if (!document.webkitFullscreenElement) {
                lockExam();
            }
        });

        if (isMobile) {
            // Mobile: detect touch on status/notification bar area
            document.addEventListener('touchstart', function(e) {
                const touch = e.touches[0];
                if (touch && touch.clientY < 10) {
                    lockExam();
                }
            }, { passive: true });
        }
    </script>

    <!-- Lock Overlay -->
    <div id="lockOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:9999;align-items:center;justify-content:center;flex-direction:column;text-align:center;">
        <div style="animation:slideUp 0.3s ease;">
            <i class="fas fa-lock" style="font-size:72px;color:#ef4444;margin-bottom:24px;"></i>
            <h2 style="color:white;font-size:28px;margin-bottom:12px;">Ujian Terkunci!</h2>
            <p style="color:#94a3b8;font-size:16px;max-width:400px;line-height:1.6;">
                Terdeteksi aktivitas di luar ujian.<br>
                Akun Anda telah dikunci. Hubungi pengawas untuk membuka kunci.
            </p>
            <div style="margin-top:24px;padding:14px 28px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:12px;color:#f87171;font-size:14px;">
                <i class="fas fa-exclamation-triangle"></i> Mengalihkan ke halaman login...
            </div>
        </div>
    </div>
</body>
</html>

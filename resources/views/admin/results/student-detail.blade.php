@extends('layouts.admin')
@section('title', 'Detail Jawaban: ' . $student->nama)

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h3 style="font-size:18px;">{{ $student->nama }}</h3>
        <p style="color:var(--text-secondary);font-size:13px;">{{ $student->nisn }} • {{ $session->nama_sesi }}</p>
    </div>
    @if($result)
    <div style="text-align:right;">
        <div style="font-size:36px;font-weight:800;color:{{ $result->lulus ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($result->skor, 1) }}</div>
        <span class="badge {{ $result->lulus ? 'badge-success' : 'badge-danger' }}">{{ $result->lulus ? 'LULUS' : 'TIDAK LULUS' }}</span>
    </div>
    @endif
</div>

@foreach($questions as $i => $q)
@php $answer = $answers->get($q->id); @endphp
<div class="card" style="margin-bottom:16px;">
    <div class="card-header">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $answer && $answer->is_correct ? 'var(--success)' : ($answer ? 'var(--danger)' : 'var(--border-color)') }};color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">{{ $i + 1 }}</div>
            <span class="badge badge-info" style="font-size:11px;">{{ str_replace('_', ' ', ucfirst($q->tipe)) }}</span>
        </div>
        <div>
            @if($answer)
                @if($answer->is_correct)<span class="badge badge-success"><i class="fas fa-check"></i> Benar</span>
                @else<span class="badge badge-danger"><i class="fas fa-times"></i> Salah</span>@endif
                <span class="badge badge-purple">{{ $answer->skor ?? 0 }}/{{ $q->bobot }}</span>
            @else
                <span class="badge badge-warning">Tidak dijawab</span>
            @endif
        </div>
    </div>
    <div class="card-body-padded">
        <div style="margin-bottom:12px;">{!! nl2br(e($q->pertanyaan)) !!}</div>

        @if(in_array($q->tipe, ['multiple_choice', 'multiple_answer', 'true_false']))
            @foreach($q->options as $opt)
            @php
                $isSelected = false;
                if ($answer) {
                    if ($q->tipe === 'multiple_answer') {
                        $answeredIds = json_decode($answer->jawaban, true) ?? [];
                        $isSelected = in_array($opt->id, $answeredIds);
                    } else {
                        $isSelected = $answer->jawaban == $opt->id;
                    }
                }
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:8px 14px;border-radius:8px;margin-bottom:4px;
                background:{{ $opt->is_correct ? 'rgba(16,185,129,0.1)' : ($isSelected ? 'rgba(239,68,68,0.1)' : 'rgba(15,23,42,0.3)') }};
                border:1px solid {{ $opt->is_correct ? 'rgba(16,185,129,0.3)' : ($isSelected ? 'rgba(239,68,68,0.3)' : 'var(--border-color)') }};">
                @if($opt->is_correct)<i class="fas fa-check-circle" style="color:var(--success);"></i>
                @elseif($isSelected)<i class="fas fa-times-circle" style="color:var(--danger);"></i>
                @else<i class="far fa-circle" style="color:var(--text-secondary);"></i>@endif
                <span>{{ $opt->teks_opsi }} @if($isSelected)<strong>(dipilih)</strong>@endif</span>
            </div>
            @endforeach
        @elseif($q->tipe === 'short_answer' || $q->tipe === 'essay')
            <div style="padding:10px 14px;border-radius:8px;background:rgba(15,23,42,0.3);border:1px solid var(--border-color);margin-bottom:6px;">
                <strong style="color:var(--text-secondary);font-size:12px;">Jawaban:</strong><br>
                {{ $answer->jawaban ?? '-' }}
            </div>
            @if($q->tipe === 'short_answer')
                <div style="padding:8px 14px;border-radius:8px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);font-size:13px;">
                    <i class="fas fa-check" style="color:var(--success);"></i> Jawaban benar: {{ $q->options->first()->teks_opsi ?? '' }}
                </div>
            @endif
        @endif
    </div>
</div>
@endforeach
@endsection

@extends('layouts.default')

@section('title', '打刻')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container center">
            @php
                // PG01: 勤怠登録画面のステータス判定
                $statusLabel = '勤務外';
                $isPunchedOut = false;
                $isOnBreak = false;

                if ($attendance) {
                    $isPunchedOut = !is_null($attendance->punch_out);

                    // ★ここを start/end から in/out に修正
                    $isOnBreak = (!is_null($attendance->break_in) && is_null($attendance->break_out)) ||
                        (!is_null($attendance->break2_in) && is_null($attendance->break2_out));

                    if ($isPunchedOut) {
                        $statusLabel = '退勤済';
                    } elseif ($isOnBreak) {
                        $statusLabel = '休憩中';
                    } else {
                        $statusLabel = '出勤中';
                    }
                }

                $days = ['日', '月', '火', '水', '木', '金', '土'];
                $now = \Carbon\Carbon::now();
            @endphp

            <div class="status-badge">{{ $statusLabel }}</div>

            <div class="datetime-display">
                <div class="date-display">{{ $now->format('Y年n月j日') }}({{ $days[$now->dayOfWeek] }})</div>
                <div class="time-display">{{ $now->format('H:i') }}</div>
            </div>

            <div class="stamp-form-container">
                <form action="{{ route('attendance.stamp') }}" method="post">
                    @csrf
                    <div class="button-group">
                        @if (!$attendance)
                            {{-- ステータス：勤務外 --}}
                            <button type="submit" name="type" value="punch_in" class="btn--black-rect">出勤</button>
                        @elseif ($isPunchedOut)
                            {{-- ステータス：退勤済 --}}
                            <p class="finish-message"><strong>お疲れ様でした。</strong></p>
                        @elseif ($isOnBreak)
                            {{-- ステータス：休憩中 --}}
                            {{-- ★valueを break_out に修正 --}}
                            <button type="submit" name="type" value="break_out" class="btn--white-rect">休憩戻</button>
                        @else
                            {{-- ステータス：出勤中 --}}
                            <button type="submit" name="type" value="punch_out" class="btn--black-rect">退勤</button>
                            {{-- ★valueを break_in に修正 --}}
                            <button type="submit" name="type" value="break_in" class="btn--white-rect">休憩入</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
@extends('layouts.default')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            <h2 class="page-title">勤怠一覧</h2>

            {{-- 月移動ナビゲーション --}}
            <div class="date-nav-card">
                {{-- 前月 --}}
                <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="nav-link">← 前月</a>

                {{-- 中央：カレンダー機能付き表示 --}}
                <div class="current-date-display">
                    <form action="{{ route('attendance.list') }}" method="get" id="month-form"
                        style="display: flex; align-items: center;">
                        {{-- 非表示の月選択入力欄 --}}
                        <input type="month" name="month" id="month-input" value="{{ $currentMonth->format('Y-m') }}"
                            onchange="this.form.submit()" style="display: none;">

                        {{-- アイコンと年月テキストをクリックでカレンダー起動 --}}
                        <div onclick="document.getElementById('month-input').showPicker()"
                            style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <span class="calendar-icon">📅</span>
                            <span style="font-size: 20px; font-weight: bold;">
                                {{-- 💡 月情報を画面に出力します --}}
                                {{ $currentMonth->format('Y/m') }}
                            </span>
                        </div>
                    </form>
                </div>

                {{-- 次月 --}}
                <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="nav-link">次月 →</a>
            </div>

            <div class="table-card">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>合計</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            @php
                                // 各種時間の計算ロジック
                                $totalBreakMinutes = 0;

                                // 休憩1の計算
                                if ($attendance->break_in && $attendance->break_out) {
                                    $breakIn = \Carbon\Carbon::parse($attendance->break_in);
                                    $breakOut = \Carbon\Carbon::parse($attendance->break_out);
                                    $totalBreakMinutes += $breakOut->diffInMinutes($breakIn);
                                }

                                // 休憩2の計算
                                if ($attendance->break2_in && $attendance->break2_out) {
                                    $break2In = \Carbon\Carbon::parse($attendance->break2_in);
                                    $break2Out = \Carbon\Carbon::parse($attendance->break2_out);
                                    $totalBreakMinutes += $break2Out->diffInMinutes($break2In);
                                }

                                // 休憩合計時間のフォーマット（休憩がなければ空白）
                                $breakTimeStr = '';
                                if ($totalBreakMinutes > 0) {
                                    $hours = floor($totalBreakMinutes / 60);
                                    $minutes = $totalBreakMinutes % 60;
                                    $breakTimeStr = sprintf('%02d:%02d', $hours, $minutes);
                                }

                                // 勤務合計時間の計算（出勤・退勤が揃っている場合のみ）
                                $totalWorkStr = '';
                                if ($attendance->punch_in && $attendance->punch_out) {
                                    $punchIn = \Carbon\Carbon::parse($attendance->punch_in);
                                    $punchOut = \Carbon\Carbon::parse($attendance->punch_out);
                                    $stayMinutes = $punchOut->diffInMinutes($punchIn);

                                    // 滞在時間から休憩時間を引く
                                    $workMinutes = $stayMinutes - $totalBreakMinutes;
                                    if ($workMinutes < 0) {
                                        $workMinutes = 0;
                                    }

                                    $wHours = floor($workMinutes / 60);
                                    $wMinutes = $workMinutes % 60;
                                    $totalWorkStr = sprintf('%02d:%02d', $wHours, $wMinutes);
                                }
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}</td>
                                {{-- 💡 データがない場合は三項演算子で '' (空白) を徹底します --}}
                                <td>{{ $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '' }}</td>
                                <td>{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '' }}</td>
                                <td>{{ $breakTimeStr }}</td>
                                <td>{{ $totalWorkStr }}</td>
                                <td>
                                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}"
                                        class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
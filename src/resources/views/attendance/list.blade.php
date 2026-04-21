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
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d') }}</td>
                                <td>{{ $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '' }}</td>
                                <td>{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '' }}</td>
                                <td>
                                    @php
    // 休憩時間の合計計算（分単位などのロジックは後ほど追加可能）
    // ここではシンプルに1回目と2回目があるかを表示
    $break1 = $attendance->break_start ? '有' : '-';
                                    @endphp
                                    {{ $break1 }}
                                </td>
                                <td>
                                    {{-- 勤務合計時間の表示ロジック（必要であれば） --}}
                                </td>
                                <td>
                                    {{-- PG03: 勤怠詳細画面へのリンク --}}
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
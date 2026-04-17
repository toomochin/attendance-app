@extends('layouts.default')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            {{-- タイトル --}}
            <h2 class="page-title">{{ $staff->name }}さんの勤怠</h2>

            {{-- 月移動ナビゲーション --}}
            <div class="date-nav-card">
                {{-- 前月 --}}
                <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prevMonth]) }}"
                    class="nav-link">← 前月</a>

                {{-- 中央：カレンダー機能付き表示 --}}
                <div class="current-date-display">
                    <form action="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}" method="get" id="month-form"
                        style="display: flex; align-items: center;">
                        {{-- 非表示の月選択入力欄 --}}
                        <input type="month" name="month" id="month-input"
                            value="{{ \Carbon\Carbon::parse($currentMonth)->format('Y-m') }}" onchange="this.form.submit()"
                            style="display: none;">

                        {{-- アイコンと年月テキストをクリックでカレンダー起動 --}}
                        <div onclick="document.getElementById('month-input').showPicker()"
                            style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <span class="calendar-icon">📅</span>
                            <span style="font-size: 20px; font-weight: bold;">
                                {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}
                            </span>
                        </div>
                    </form>
                </div>

                {{-- 次月 --}}
                <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}"
                    class="nav-link">翌月 →</a>
            </div>

            {{-- テーブル --}}
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
                                <td>{{ $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '-' }}</td>
                                <td>{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '-' }}</td>
                                <td>{{ $attendance->total_break }}</td>
                                <td>{{ $attendance->total_time }}</td>
                                <td>
                                    {{-- 各日付の勤怠詳細(PG09)へ遷移 --}}
                                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}"
                                        class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- 4. CSV出力ボタン --}}
            <div class="csv-export-container">
                <a href="{{ route('admin.attendance.export', ['id' => $staff->id, 'month' => request('month')]) }}"
                    class="btn-csv">
                    CSV出力
                </a>
            </div>
        </div>
    </main>
@endsection
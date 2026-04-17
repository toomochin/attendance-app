@extends('layouts.default')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            {{-- タイトル --}}
            <h2 class="page-title">{{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠</h2>

            {{-- 日付ナビゲーション --}}
            <div class="date-nav-card">
                <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="nav-link">
                    <span class="arrow-left"></span>← 前日
                </a>

                {{-- 中央：カレンダー機能付き表示 --}}
                <div class="current-date-display">
                    <form action="{{ route('admin.attendance.list') }}" method="get" id=" date-form"
                        style="display: flex; align-items: center;">
                        {{-- inputは完全に隠す --}}
                        <input type="date" name="date" id="date-input" value="{{ $currentDate }}" onchange="this.form.submit()"
                            style="display: none;">

                        {{-- labelではなく、onclickイベントをつけたdivを使用 --}}
                        <div onclick="document.getElementById('date-input').showPicker()" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <span class=" calendar-icon">📅</span>
                            <span style="font-size: 20px; font-weight: bold;">
                                {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
                            </span>
                        </div>
                    </form>
                </div>

                <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="nav-link">
                    翌日 →<span class="arrow-right"></span>
                </a>
            </div>

            {{-- テーブルカード --}}
            <div class="table-card">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>名前</th>
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
                                <td>{{ $attendance->user->name }}</td>
                                <td>{{ $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '-' }}</td>
                                <td>{{ $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '-' }}</td>
                                <td>{{ $attendance->total_break }}</td>
                                <td>{{ $attendance->total_time }}</td>
                                <td>
                                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
@endsection
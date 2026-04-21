@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
    <style>
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
    </style>
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            <h2 class="page-title">勤怠詳細</h2>

            <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="post"
                class="table-card p-30">
                @csrf

                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="date-flex">
                                <span class="date-text">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span class="date-text ml-20">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="time-input-group">
                                <input type="text" name="punch_in" class="input-time"
                                    value="{{ old('punch_in', $attendance->punch_in ? date('H:i', strtotime($attendance->punch_in)) : '') }}">
                                <span class="tilde">〜</span>
                                <input type="text" name="punch_out" class="input-time"
                                    value="{{ old('punch_out', $attendance->punch_out ? date('H:i', strtotime($attendance->punch_out)) : '') }}">
                            </div>
                            @error('punch_in') <span class="error-message">{{ $message }}</span> @enderror
                            @error('punch_out') <span class="error-message">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    {{-- 休憩 (break_start -> break_in に修正) --}}
                    <tr>
                        <th>休憩</th>
                        <td>
                            <div class="time-input-group">
                                <input type="text" name="break_in" class="input-time"
                                    value="{{ old('break_in', $attendance->break_in ? date('H:i', strtotime($attendance->break_in)) : '') }}">
                                <span class="tilde">〜</span>
                                <input type="text" name="break_out" class="input-time"
                                    value="{{ old('break_out', $attendance->break_out ? date('H:i', strtotime($attendance->break_out)) : '') }}">
                            </div>
                            @error('break_in') <span class="error-message">{{ $message }}</span> @enderror
                            @error('break_out') <span class="error-message">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="remark" class="input-textarea">{{ old('remark', $attendance->remark) }}</textarea>
                            @error('remark') <span class="error-message">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                </table>

                <div class="form-actions-right">
                    <button type="submit" class="btn--black-rect">修正</button>
                </div>
            </form>
        </div>
    </main>
@endsection
@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
        @include('components.header')

        @php
    // コントローラーから渡された変数 pendingRequest を使用
    $isPending = isset($pendingRequest) && $pendingRequest;
    $display = $isPending ? $pendingRequest : $attendance;

    // 時刻整形関数
    $t = function ($val) {
        return $val ? date('H:i', strtotime($val)) : '';
    };
        @endphp

        <main class="attendance-layout">
            <div class="attendance-container">
                <h2 class="page-title">勤怠詳細</h2>

                <div class="table-card p-30">
                    <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="post">
                        @csrf
                        <table class="detail-table">
                            <tr>
                                <th>名前</th>
                                <td><span class="detail-text">{{ Auth::user()->name }}</span></td>
                            </tr>
                            <tr>
                                <th>日付</th>
                                <td>
                                    <div class="date-flex">
                                        <span
                                            class="detail-text">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                        <span
                                            class="detail-text ml-20">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>出勤・退勤</th>
                                <td>
                                    @if($isPending)
                                        <span class="detail-text">{{ $t($display->punch_in) }} 〜
                                            {{ $t($display->punch_out) }}</span>
                                    @else
                                        <div class="time-input-group">
                                            <input type="text" name="punch_in" class="input-time"
                                                value="{{ old('punch_in', $t($display->punch_in)) }}">
                                            <span class="tilde">〜</span>
                                            <input type="text" name="punch_out" class="input-time"
                                                value="{{ old('punch_out', $t($display->punch_out)) }}">
                                        </div>
                                        @error('punch_in') <p style="color:red; font-size:12px;">{{ $message }}</p> @enderror
                                        @error('punch_out') <p style="color:red; font-size:12px;">{{ $message }}</p> @enderror
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>休憩</th>
                                <td>
                                    @if($isPending)
                                        <span class="detail-text">{{ $t($display->break_in) }} 〜
                                            {{ $t($display->break_out) }}</span>
                                    @else
                                        <div class="time-input-group">
                                            <input type="text" name="break_in" class="input-time"
                                                value="{{ old('break_in', $t($display->break_in)) }}">
                                            <span class="tilde">〜</span>
                                            <input type="text" name="break_out" class="input-time"
                                                value="{{ old('break_out', $t($display->break_out)) }}">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>休憩2</th>
                                <td>
                                    @if($isPending)
                                        <span class="detail-text">{{ $t($display->break2_in) }} 〜
                                            {{ $t($display->break2_out) }}</span>
                                    @else
                                        <div class="time-input-group">
                                            <input type="text" name="break2_in" class="input-time"
                                                value="{{ old('break2_in', $t($display->break2_in)) }}">
                                            <span class="tilde">〜</span>
                                            <input type="text" name="break2_out" class="input-time"
                                                value="{{ old('break2_out', $t($display->break2_out)) }}">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>備考</th>
                                <td>
                                    @if($isPending)
                                        <p class="detail-text" style="white-space: pre-wrap;">{{ $display->remark }}</p>
                                    @else
                                        <textarea name="remark"
                                            class="input-textarea">{{ old('remark', $display->remark) }}</textarea>
                                        @error('remark') <p style="color:red; font-size:12px;">{{ $message }}</p> @enderror
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div class="form-actions-right">
                            {{-- $pendingRequest が存在（nullでない）＝ 承認待ちがある場合 --}}
                            @if(isset($pendingRequest) && $pendingRequest)
                                <p style="color: red; font-weight: bold;">*承認待ちのため修正できません</p>
                            @else
                                <button type="submit" class="btn-update">修正</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </main>
@endsection
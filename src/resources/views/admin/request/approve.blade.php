@extends('layouts.default')

@section('title', '修正申請承認')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            {{-- タイトルをデザインに合わせて調整 --}}
            <h2 class="page-title">勤怠詳細（申請内容の確認）</h2>

            <div class="table-card p-30">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        {{-- $request（申請データ）経由でユーザー名を表示 --}}
                        <td>{{ $request->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="date-flex">
                                <span
                                    class="date-text">{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年') }}</span>
                                <span
                                    class="date-text ml-20">{{ \Carbon\Carbon::parse($request->attendance->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            {{-- 修正「後」の時間を表示 --}}
                            {{ date('H:i', strtotime($request->punch_in)) }}
                            <span class="tilde" style="margin: 0 10px;">〜</span>
                            {{ $request->punch_out ? date('H:i', strtotime($request->punch_out)) : '' }}
                        </td>
                    </tr>
                    <tr>
                        {{-- 休憩1の修正 --}}
                    <tr>
                        <th>休憩</th>
                        <td>
                            @if($request->break_in || $request->break_out)
                                {{ $request->break_in ? date('H:i', strtotime($request->break_in)) : '' }}
                                <span class="tilde" style="margin: 0 10px;">〜</span>
                                {{ $request->break_out ? date('H:i', strtotime($request->break_out)) : '' }}
                            @endif
                        </td>
                    </tr>

                    {{-- 休憩2の修正 --}}
                    <tr>
                        <th>休憩2</th>
                        <td>
                            @if($request->break2_in || $request->break2_out)
                                {{ $request->break2_in ? date('H:i', strtotime($request->break2_in)) : '' }}
                                <span class="tilde" style="margin: 0 10px;">〜</span>
                                {{ $request->break2_out ? date('H:i', strtotime($request->break2_out)) : '' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            {!! nl2br(e($request->remark)) !!}
                        </td>
                    </tr>
                </table>

                {{-- 承認フォーム部分 --}}
                <div class="form-actions-right" style="margin-top: 30px; display: flex; justify-content: flex-end;">
                    @if($request->status == 1)
                        <button type="button" class="btn-approve" disabled
                            style="background-color: #ccc; color: #fff; border: none; cursor: not-allowed;">
                            承認済み
                        </button>
                    @else
                        {{-- 二重フォームを解消し、シンプルな１つの送信ボタンにします --}}
                        <form action="{{ route('admin.request.approve', ['attendance_correct_request_id' => $request->id]) }}"
                            method="post">
                            @csrf
                            <button type="submit" class="btn-approve">
                                承認
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
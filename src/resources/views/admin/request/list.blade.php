@extends('layouts.default')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            <h2 class="page-title">申請一覧</h2>

            {{-- タブ切り替え（status 0:承認待ち, 1:承認済み） --}}
            <div class="tabs" style="display: flex; gap: 30px; border-bottom: 1px solid #ddd; margin-bottom: 25px;">
                <a href="{{ route('request.list', ['status' => 0]) }}"
                    style="text-decoration: none; padding-bottom: 10px; color: {{ ($status ?? 0) == 0 ? '#000' : '#999' }}; border-bottom: {{ ($status ?? 0) == 0 ? '2px solid #000' : 'none' }}; font-weight: bold;">
                    承認待ち
                </a>
                <a href="{{ route('request.list', ['status' => 1]) }}"
                    style="text-decoration: none; padding-bottom: 10px; color: {{ ($status ?? 0) == 1 ? '#000' : '#999' }}; border-bottom: {{ ($status ?? 0) == 1 ? '2px solid #000' : 'none' }}; font-weight: bold;">
                    承認済み
                </a>
            </div>

            <div class="table-card">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                {{-- 修正申請テーブルの status を使用 --}}
                                <td>{{ $req->status == 0 ? '承認待ち' : '承認済み' }}</td>
                                <td>{{ $req->user->name }}</td>
                                {{-- 勤怠データの年月を表示 --}}
                                <td>{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                                {{-- 申請理由 --}}
                                <td>{{ Str::limit($req->remark, 30) }}</td>
                                {{-- 申請が行われた日時 --}}
                                <td>{{ $req->created_at->format('Y/m/d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.request.approve', ['attendance_correct_request_id' => $req->id]) }}"
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
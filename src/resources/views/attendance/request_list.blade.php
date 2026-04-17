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

            {{-- タブ切り替え --}}
            <div class="tabs" style="display: flex; gap: 30px; border-bottom: 1px solid #ddd; margin-bottom: 25px;">
                <a href="{{ route('request.list', ['status' => 0]) }}"
                    style="text-decoration: none; padding-bottom: 10px; color: {{ $status == 0 ? '#000' : '#999' }}; border-bottom: {{ $status == 0 ? '2px solid #000' : 'none' }}; font-weight: bold;">
                    承認待ち
                </a>
                <a href="{{ route('request.list', ['status' => 1]) }}"
                    style="text-decoration: none; padding-bottom: 10px; color: {{ $status == 1 ? '#000' : '#999' }}; border-bottom: {{ $status == 1 ? '2px solid #000' : 'none' }}; font-weight: bold;">
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
                                <td>{{ $req->status == 0 ? '承認待ち' : '承認済み' }}</td>
                                <td>{{ Auth::user()->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                                <td>{{ Str::limit($req->remark, 30) }}</td>
                                <td>{{ $req->created_at->format('Y/m/d') }}</td>
                                <td>
                                    {{-- 詳細画面（PG05）へのリンク（作成済みなら遷移可能） --}}
                                    <a href="{{ route('attendance.detail', ['id' => $req->attendance_id]) }}"
                                        class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($requests->isEmpty())
                    <p style="text-align: center; padding: 20px; color: #999;">該当する申請はありません。</p>
                @endif
            </div>
        </div>
    </main>
@endsection
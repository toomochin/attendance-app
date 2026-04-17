@extends('layouts.default')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance.css') }}">
@endsection

@section('content')
    @include('components.header')

    <main class="attendance-layout">
        <div class="attendance-container">
            <h2 class="page-title">スタッフ一覧</h2>

            <div class="table-card">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffs as $staff)
                            <tr>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->email }}</td>
                                <td>
                                    {{-- スタッフ別勤怠一覧(PG11)へのリンク --}}
                                    <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}"
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
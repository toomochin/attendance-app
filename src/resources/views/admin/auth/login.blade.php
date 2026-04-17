@extends('layouts.default')

@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/authentication.css') }}">
@endsection

@section('content')

    @include('components.header')

    <form action="/admin/login" method="post" class="authenticate center">
        @csrf
        <h1 class="page__title">管理者ログイン</h1>

        <label for="email" class="entry__name">メールアドレス</label>
        <input name="email" id="email" type="email" class="input" value="{{ old('email') }}">
        <div class="form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>

        <label for="password" class="entry__name">パスワード</label>
        <input name="password" id="password" type="password" class="input">
        <div class="form__error">
            @error('password')
                {{ $message }}
            @enderror
        </div>

        <button class="btn btn--big">管理者ログインする</button>

        {{-- 管理者画面には会員登録リンクは含めない --}}
    </form>

@endsection
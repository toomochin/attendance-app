@extends('layouts.default')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/authentication.css') }}">
@endsection

@section('content')

    @include('components.header')

    <form action="{{ route('login') }}" method="post" class="authenticate center">
        @csrf
        <h1 class="page__title">ログイン</h1>

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

        <button class="btn btn--big">ログインする</button>

        {{-- 一般ユーザーは会員登録画面へのリンクが必要 --}}
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('register') }}" class="link">会員登録はこちら</a>
        </div>
    </form>

@endsection
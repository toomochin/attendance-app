<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるか
     */
    public function authorize()
    {
        return true; // ログイン前なので true に設定
    }

    /**
     * バリデーションルール
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレス形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
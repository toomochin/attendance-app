<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AdminAuthController extends Controller
{
    /**
     * PG07: 管理者ログイン画面の表示
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function login(AdminLoginRequest $request)
    {
        // 既にバリデーション済みのデータを取得
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.attendance.list'));
        }

        return back()->withErrors([
            'email' => '認証情報が記録と一致しません。',
        ])->onlyInput('email');
    }

    /**
     * 管理者ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 既存の管理者がいない場合のみ作成する（二重登録エラー防止）
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // メールアドレスで重複チェック
            [
                'name' => '管理者 太郎',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'role' => 1, // 管理者フラグ（仕様書：1）
            ]
        );
    }
}
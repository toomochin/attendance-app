<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User; // AdminではなくUserを使う

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function PG07_メールアドレスが未入力の場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpassword',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function PG07_パスワードが未入力の場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /** @test */
    public function PG07_管理者ログインが成功し管理者勤怠一覧にリダイレクトされる()
    {
        // Userモデルを使用して管理者を作成
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'),
            'role' => 1,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect(route('admin.attendance.list'));
        // ガード名が 'admin' であることを確認
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}
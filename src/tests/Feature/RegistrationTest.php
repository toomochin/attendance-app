<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録画面が表示される()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test */
    public function 正しい情報を入力すると会員登録が成功しログインされる()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 💡 データベースにユーザーが保存されているか検証
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 💡 登録後に自動でログイン状態になり打刻画面に遷移するか検証
        $this->assertAuthenticated();
        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function 名前が未入力の場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /** @test */
    public function メールアドレスが未入力の場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /** @test */
    public function パスワードが8文字未満の場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '7charas',
            'password_confirmation' => '7charas',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /** @test */
    public function パスワードが一致しない場合にバリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }
}
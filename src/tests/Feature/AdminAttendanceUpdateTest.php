<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Userモデルで管理者(role:1)を作成
        $this->admin = User::factory()->create(['role' => 1]);
    }

    /** @test */
    public function PG08_全スタッフの当日の勤怠情報が正しく表示される()
    {
        // ID 8: 管理者勤怠一覧画面にスタッフ名が表示される
        $user = User::factory()->create(['name' => '管理者テスト用スタッフ']);
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('管理者テスト用スタッフ');
    }

    /** @test */
    public function PG10_全スタッフの名前とメールアドレスが一覧に表示される()
    {
        // ID 8: スタッフ一覧画面
        User::factory()->create(['name' => 'スタッフA', 'email' => 'a@example.com']);
        User::factory()->create(['name' => 'スタッフB', 'email' => 'b@example.com']);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('a@example.com');
        $response->assertSee('スタッフB');
        $response->assertSee('b@example.com');
    }

    /** @test */
    public function PG11_スタッフ別の勤怠一覧画面が表示される()
    {
        // ID 9: スタッフ別勤怠一覧
        $user = User::factory()->create(['name' => '個別スタッフ']);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertStatus(200);
        $response->assertSee('個別スタッフ');
    }

    /** @test */
    public function PG09_管理者が勤怠詳細を確認できる()
    {
        // ID 11: 管理者用勤怠詳細
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function PG09_管理者が勤怠データを直接修正して上書き保存できる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        // 💡 管理者が勤怠詳細画面から更新ポストリクエストを送信して直接書き換える検証
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '10:00',
            'punch_out' => '19:00',
            'break_in' => '12:00',
            'break_out' => '13:00',
            'remark' => '管理者が直接データを修正します',
        ]);

        $response->assertRedirect(route('admin.attendance.detail', ['id' => $attendance->id]));

        // 💡 データベースが直接上書き変更されているか徹底検証
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'punch_in' => '10:00:00',
            'punch_out' => '19:00:00',
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }
}
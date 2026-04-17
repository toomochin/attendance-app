<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // 管理者ユーザを作成 (role: 1)
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 1,
        ]);

        // 一般ユーザを作成 (role: 0)
        $this->user = User::create([
            'name' => 'General User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 0,
        ]);
    }

    /**
     * 管理者が全スタッフの勤怠一覧を見れるか (ID 11)
     */
    public function test_admin_can_see_all_users_attendance()
    {
        // 管理者ガードを指定してアクセス
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');
    }

    /**
     * 管理者が特定の勤怠詳細を見れるか (ID 09)
     */
    public function test_admin_can_see_specific_user_attendance()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        // 正しい管理者用ルートパス /admin/attendance/{id} を使用
        $response = $this->actingAs($this->admin, 'admin')->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    /**
     * 管理者が修正申請を承認できるか (ID 13)
     */
    public function test_admin_can_approve_correction_request()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $request = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'punch_in' => '10:00:00',
            'status' => 0, // 承認待ち
            'remark' => '修正テスト',
        ]);

        // 実際のルート /admin/stamp_correction_request/approve/{id} に合わせる
        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(302); // リダイレクトを確認
        $this->assertEquals(1, $request->fresh()->status); // ステータスが承認済(1)か確認
    }

    /**
     * 管理者が月移動ナビゲーションを行えるか (ID 12)
     */
    public function test_admin_can_navigate_months()
    {
        // 管理者用パスを使用
        $response = $this->actingAs($this->admin, 'admin')
            ->get('/admin/attendance/list?date=' . Carbon::now()->subDay()->format('Y-m-d'));

        $response->assertStatus(200);
    }
}
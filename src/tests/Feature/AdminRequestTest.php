<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;

class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Userモデルを使って管理者(role:1)を作成
        $this->admin = User::factory()->create([
            'role' => 1,
        ]);
    }

    /** @test */
    public function PG12_全ユーザーの承認待ち修正申請が正しく表示される()
    {
        // ID 14: 承認待ちの修正申請が全て表示されている
        $user1 = User::factory()->create(['name' => 'スタッフA', 'role' => 0]);
        $user2 = User::factory()->create(['name' => 'スタッフB', 'role' => 0]);

        $att1 = Attendance::create(['user_id' => $user1->id, 'date' => '2026-04-01', 'punch_in' => '09:00:00']);
        $att2 = Attendance::create(['user_id' => $user2->id, 'date' => '2026-04-01', 'punch_in' => '09:00:00']);

        AttendanceCorrectRequest::create([
            'attendance_id' => $att1->id,
            'user_id' => $user1->id,
            'status' => 0,
            'remark' => '申請A',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00'
        ]);
        AttendanceCorrectRequest::create([
            'attendance_id' => $att2->id,
            'user_id' => $user2->id,
            'status' => 0,
            'remark' => '申請B',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00'
        ]);

        // ルート名を admin.request.list から request.list に変更
        $response = $this->actingAs($this->admin, 'admin')->get(route('request.list'));

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('スタッフB');
    }

    /** @test */
    public function PG12_承認済みの修正申請が正しく表示される()
    {
        // ID 15: 承認済みの修正申請が全て表示されている
        $user = User::factory()->create(['name' => 'スタッフC', 'role' => 0]);
        $att = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'punch_in' => '09:00:00']);

        AttendanceCorrectRequest::create([
            'attendance_id' => $att->id,
            'user_id' => $user->id,
            'status' => 1,
            'remark' => '承認済みテスト',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00'
        ]);

        // ルート名を admin.request.list から request.list に変更
        $response = $this->actingAs($this->admin, 'admin')->get(route('request.list', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('スタッフC');
        $response->assertSee('承認済みテスト');
    }

    /** @test */
    public function PG13_承認処理を行うと元の勤怠データが更新される()
    {
        // ID 16: 修正申請を承認すると、勤怠データに反映される
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-04-01', 'punch_in' => '09:00:00', 'punch_out' => '17:00:00']);

        $correctRequest = AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 0,
            'remark' => '18時退勤に修正',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.request.approve', ['attendance_correct_request_id' => $correctRequest->id]));

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'punch_out' => '18:00:00',
        ]);

        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $correctRequest->id,
            'status' => 1,
        ]);
    }
}
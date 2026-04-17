<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 0]);
    }

    /** @test */
    public function 出勤時刻より前の退勤時刻はバリデーションエラーになる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'punch_out' => '08:00', // 出勤より前
            'remark' => 'エラーテスト',
        ]);

        $response->assertSessionHasErrors(['punch_out']);
    }

    /** @test */
    public function 出勤時刻より前の休憩開始時刻はバリデーションエラーになる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'break_in' => '08:30', // 出勤より前
            'punch_out' => '18:00',
            'remark' => 'エラーテスト',
        ]);

        $response->assertSessionHasErrors(['break_in']);
    }

    /** @test */
    public function 退勤時刻より後の休憩終了時刻はバリデーションエラーになる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'punch_out' => '18:00',
            'break_out' => '18:30', // 退勤より後
            'remark' => 'エラーテスト',
        ]);

        $response->assertSessionHasErrors(['break_out']);
    }

    /** @test */
    public function 備考が255文字を超える場合はバリデーションエラーになる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'punch_out' => '18:00',
            'remark' => str_repeat('あ', 256), // 256文字
        ]);

        $response->assertSessionHasErrors(['remark']);
    }
}
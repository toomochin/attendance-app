<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStampTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function PG01_現在の日時が正しい形式で表示されている()
    {
        $now = Carbon::now();
        // 画面定義書のパス /attendance へアクセス
        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($now->format('Y年n月j日'));
        $response->assertSee($now->format('H:i'));
    }

    /** @test */
    public function PG01_ステータスが勤務外の場合に正しく表示され出勤ボタンが機能する()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('勤務外'); // テスト期待値
        $response->assertSee('出勤');

        $this->actingAs($this->user)->post(route('attendance.stamp'), ['type' => 'punch_in']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function PG01_出勤中はステータスが出勤中になり退勤と休憩入ボタンが表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function PG01_休憩中はステータスが休憩中になり休憩戻ボタンが表示される()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
            'break_in' => '12:00:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function PG01_退勤後はステータスが退勤済になり出勤ボタンが表示されない()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSee('退勤済'); // テスト期待値
        $response->assertDontSee('出勤');
    }
}
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function PG02_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);
        // 表示形式を 'Y年n月' から 'Y/m' に修正
        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    /** @test */
    public function PG02_前月を押下した時に表示月の前月の情報が表示される()
    {
        $currentMonth = Carbon::now();
        $prevMonth = $currentMonth->copy()->subMonth();

        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . $prevMonth->format('Y-m'));

        $response->assertStatus(200);
        // 表示形式を 'Y年n月' から 'Y/m' に修正
        $response->assertSee($prevMonth->format('Y/m'));
    }

    /** @test */
    public function PG02_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth();

        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        // 表示形式を 'Y年n月' から 'Y/m' に修正
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /** @test */
    public function PG02_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $detailUrl = route('attendance.detail', ['id' => $attendance->id]);
        $response = $this->actingAs($this->user)->get($detailUrl);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }

    /** @test */
    public function PG03_備考が未入力の場合にバリデーションメッセージが表示される()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'punch_out' => '18:00',
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark' => '備考を記入してください']);
    }

    /** @test */
    public function PG03_修正申請が正しく処理され一覧画面にリダイレクトされる()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.update', ['id' => $attendance->id]), [
            'punch_in' => '09:00',
            'punch_out' => '19:00',
            'remark' => '残業のため修正申請します',
        ]);

        $response->assertRedirect(route('attendance.list'));

        $this->assertDatabaseHas('attendance_correct_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'remark' => '残業のため修正申請します',
            'status' => 0,
        ]);
    }

    /** @test */
    public function PG04_自分が行った修正申請がすべて表示されている()
    {
        $attendance = \App\Models\Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-01',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        \App\Models\AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'remark' => '申請一覧表示テスト',
            'status' => 0,
            'punch_in' => '09:00',
            'punch_out' => '19:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('request.list'));

        $response->assertStatus(200);
        $response->assertSee('申請一覧表示テスト');
        $response->assertSee('承認待ち');
    }

    /** @test */
    public function PG04_承認済みを押下すると承認済みの申請がすべて表示される()
    {
        $attendance = \App\Models\Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-04-02',
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',
        ]);

        \App\Models\AttendanceCorrectRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->user->id,
            'remark' => '承認済みテスト',
            'status' => 1,
            'punch_in' => '09:00',
            'punch_out' => '18:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('request.list', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト');
        $response->assertSee('承認済み');
    }
}
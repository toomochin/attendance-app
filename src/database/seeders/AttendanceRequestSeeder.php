<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceRequestSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('role', 0)->first(); // 一般スタッフを1人取得

        if ($user) {
            // --- 1. 元となる勤怠データを作成 ---
            $attendance1 = Attendance::create([
                'user_id' => $user->id,
                'date' => '2026-04-01',
                'punch_in' => '09:00:00',
                'punch_out' => '18:00:00',
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
                // 休憩2は空
            ]);

            $attendance2 = Attendance::create([
                'user_id' => $user->id,
                'date' => '2026-04-02',
                'punch_in' => '10:00:00',
                'punch_out' => '19:00:00',
                'break_in' => '13:00:00',
                'break_out' => '14:00:00',
            ]);

            // --- 2. 修正申請データ（承認待ち）を作成 ---
            AttendanceCorrectRequest::create([
                'attendance_id' => $attendance1->id,
                'user_id' => $user->id,
                'punch_in' => '09:00:00',
                'punch_out' => '18:30:00',
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
                'break2_in' => '15:00:00',
                'break2_out' => '15:15:00',
                'remark' => '打刻を忘れたため修正をお願いします。',
                'status' => 0, // 0: 承認待ち
            ]);

            // --- 3. 修正申請データ（承認済み）を作成 ---
            AttendanceCorrectRequest::create([
                'attendance_id' => $attendance2->id,
                'user_id' => $user->id,
                'punch_in' => '10:00:00',
                'punch_out' => '19:00:00',
                'break_in' => '13:00:00',
                'break_out' => '14:00:00',
                'remark' => '寝坊のため勤務時間を変更しました。',
                'status' => 1, // 1: 承認済み
            ]);
        }
    }
}
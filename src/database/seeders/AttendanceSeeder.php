<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $today = Carbon::today()->format('Y-m-d');

        // roleが0（一般ユーザー）のスタッフ全員を取得
        $users = User::where('role', 0)->get();

        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'punch_in' => '09:00:00',
                'punch_out' => '18:00:00',
                // 休憩
                'break_in' => '12:00:00',
                'break_out' => '13:00:00',
                // 休憩2
                'break2_in' => null,
                'break2_out' => null,
            ]);
        }
    }
}
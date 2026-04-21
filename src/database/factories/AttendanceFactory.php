<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // ユーザーも自動生成
            'date' => $this->faker->date(),
            'punch_in' => '09:00:00',
            'punch_out' => '18:00:00',

            'break_in' => '12:00:00',
            'break_out' => '13:00:00',

            // 休憩2も定義
            'break2_in' => null,
            'break2_out' => null,

            'remark' => $this->faker->realText(20),
        ];
    }
}
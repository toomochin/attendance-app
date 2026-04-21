<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザ1 (role: 0)
        User::create([
            'name' => '一般ユーザ1',
            'email' => 'general1@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 0, // 一般
        ]);

        // 一般ユーザ2 (role: 0)
        User::create([
            'name' => '一般ユーザ2',
            'email' => 'general2@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 0, // 一般
        ]);

        // 管理者ユーザ (role: 1)
        User::create([
            'name' => '管理者ユーザ',
            'email' => 'admin@gmail.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
            'role' => 1, // 管理者
        ]);
    }
}
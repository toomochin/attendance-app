<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    // ★追加: 役割を定数で定義（0: 一般, 1: 管理者）
    const ROLE_STAFF = 0;
    const ROLE_ADMIN = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // ★追加: roleも保存できるようにする
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'integer', // ★追加: 数値として扱うようキャスト
    ];

    /**
     * 勤怠データとのリレーション
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // ★削除済み: profile, likes, comments, items リレーション
    // フリマアプリ系のメソッドは消してスッキリさせました
}
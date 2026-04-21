<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'punch_in',
        'punch_out',
        'break_in',
        'break_out',
        'break2_in',
        'break2_out',
        'remark',
        'request_status',
    ];
    /**
     * この勤怠データに関連する修正申請を取得
     */
    public function correctRequests()
    {
        return $this->hasMany(AttendanceCorrectRequest::class);
    }
    // ユーザーとのリレーション定義
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

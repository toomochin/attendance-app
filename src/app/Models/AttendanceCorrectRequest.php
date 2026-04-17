<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'punch_in',
        'punch_out',
        'break_start',
        'break_end',
        'break2_start',
        'break2_end',
        'remark',
        'status',
    ];
    /**
     * ★これを追加：勤怠データ（Attendance）とのリレーション
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * ★これを追加：ユーザー（User）とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

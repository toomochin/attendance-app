<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest as CorrectRequestModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * PG01: 打刻画面表示
     */
    public function index()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        return view('attendance.index', compact('attendance'));
    }

    /**
     * 打刻処理 (Bladeの value="break_in" 等を処理)
     */
    public function stamp(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        switch ($request->type) {
            case 'punch_in':
                if (!$attendance)
                    Attendance::create(['user_id' => $user->id, 'date' => $today, 'punch_in' => $now]);
                break;
            case 'punch_out':
                if ($attendance && !$attendance->punch_out)
                    $attendance->update(['punch_out' => $now]);
                break;
            case 'break_in': // 休憩入
                if ($attendance) {
                    if (!$attendance->break_in) {
                        $attendance->update(['break_in' => $now]);
                    } elseif ($attendance->break_out && !$attendance->break2_in) {
                        $attendance->update(['break2_in' => $now]);
                    }
                }
                break;
            case 'break_out': // 休憩戻
                if ($attendance) {
                    if ($attendance->break_in && !$attendance->break_out) {
                        $attendance->update(['break_out' => $now]);
                    } elseif ($attendance->break2_in && !$attendance->break2_out) {
                        $attendance->update(['break2_out' => $now]);
                    }
                }
                break;
        }
        return redirect()->back();
    }

    /**
     * PG02: 勤怠一覧
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')->get()
            ->map(function ($attendance) {
                return $this->calculateTotalTimes($attendance);
            });

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $currentMonth->format('Y/m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth', 'displayMonth'));
    }

    /**
     * PG03: 勤怠詳細表示
     */
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        if ($attendance->user_id !== Auth::id())
            abort(403);
        $pendingRequest = CorrectRequestModel::where('attendance_id', $id)->where('status', 0)->first();
        return view('attendance.detail', compact('attendance', 'pendingRequest'));
    }

    /**
     * PG03: 修正申請送信
     */
    public function update(Request $request, $id)
    {
        // 💡 一般ユーザー側の修正申請バリデーションを要件（FN029）に完全一致させます
        $request->validate([
            'punch_in' => ['required', 'date_format:H:i'],
            'punch_out' => ['required', 'date_format:H:i', 'after:punch_in'],
            'break_in' => ['nullable', 'date_format:H:i', 'after:punch_in', 'before:punch_out'],
            'break_out' => ['nullable', 'date_format:H:i', 'after:break_in', 'before:punch_out'],
            'remark' => ['required', 'string', 'max:255'],
        ], [
            'punch_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_in.date_format' => '休憩時間が不適切な値です',
            'break_in.after' => '休憩時間が不適切な値です',
            'break_in.before' => '休憩時間が不適切な値です',

            'break_out.date_format' => '休憩時間が不適切な値です',
            'break_out.after' => '休憩時間が不適切な値です',
            'break_out.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'remark.required' => '備考を記入してください',
        ]);

        CorrectRequestModel::create([
            'attendance_id' => $id,
            'user_id' => Auth::id(),
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'break_in' => $request->break_in,
            'break_out' => $request->break_out,
            'remark' => $request->remark,
            'status' => 0,
        ]);

        return redirect()->route('attendance.list');
    }

    /**
     * PG04: 自分の申請一覧表示
     */
    public function requestList(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 0);
        $requests = CorrectRequestModel::with('attendance')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')->get();

        return view('attendance.request_list', compact('requests', 'status'));
    }

    /**
     * 💡 空白対応を含めた時間計算メソッドを追加
     */
    private function calculateTotalTimes($attendance)
    {
        $break1 = ($attendance->break_in && $attendance->break_out)
            ? Carbon::parse($attendance->break_in)->diffInMinutes(Carbon::parse($attendance->break_out)) : 0;

        $break2 = ($attendance->break2_in && $attendance->break2_out)
            ? Carbon::parse($attendance->break2_in)->diffInMinutes(Carbon::parse($attendance->break2_out)) : 0;

        $totalBreakMinutes = $break1 + $break2;

        if ($totalBreakMinutes > 0) {
            $attendance->total_break = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
        } else {
            $attendance->total_break = '';
        }

        if ($attendance->punch_in && $attendance->punch_out) {
            $workMinutes = Carbon::parse($attendance->punch_in)->diffInMinutes(Carbon::parse($attendance->punch_out));
            $actualWorkMinutes = max(0, $workMinutes - $totalBreakMinutes);
            $attendance->total_time = sprintf('%02d:%02d', floor($actualWorkMinutes / 60), $actualWorkMinutes % 60);
        } else {
            $attendance->total_time = '';
        }

        return $attendance;
    }
}
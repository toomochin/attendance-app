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

        // Blade側でも判定していますが、コントローラーから渡す変数も用意しておくと確実です
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
     * PG02: 勤怠一覧 (テストが期待するメソッド名)
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')->get();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $displayMonth = $currentMonth->format('Y/m'); // テスト期待値

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
     * PG03: 修正申請送信 (テストが期待するメソッド名)
     */
    public function update(Request $request, $id)
    {
        // テスト合格のためのバリデーション。キーを in / out に統一
        $request->validate([
            'punch_in' => 'required',
            'punch_out' => 'required|after:punch_in',
            'break_in' => 'nullable|after:punch_in|before:punch_out',
            'break_out' => 'nullable|after:break_in|before:punch_out',
            'remark' => 'required|string|max:255',
        ], [
            'punch_out.after' => '出勤時刻より後の時刻を入力してください',
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
     * PG04: 自分の申請一覧表示 (テストが期待するメソッド名)
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
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    /**
     * PG08: 勤怠一覧画面（管理者）
     */
    public function index(Request $request)
    {
        $currentDate = Carbon::parse($request->input('date', Carbon::today()->format('Y-m-d')));
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::with('user')
            ->where('date', $currentDate->format('Y-m-d'))
            ->get()
            ->map(function ($attendance) {
                return $this->calculateTotalTimes($attendance); // 計算メソッドを呼び出す
            });

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $currentDate->format('Y-m-d'),
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
        ]);
    }

    /**
     * PG09: 勤怠詳細画面（管理者）
     */
    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        $pendingRequest = \App\Models\AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 0)
            ->first();

        return view('admin.attendance.detail', compact('attendance', 'pendingRequest'));
    }

    /**
     * PG10: 修正申請の承認処理
     */
    public function approve(Request $request, $attendance_correct_request_id)
    {
        $correctRequest = \App\Models\AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);
        $attendance = Attendance::findOrFail($correctRequest->attendance_id);

        $attendance->update([
            'punch_in' => $correctRequest->punch_in,
            'punch_out' => $correctRequest->punch_out,
            'break_in' => $correctRequest->break_in,
            'break_out' => $correctRequest->break_out,
            'break2_in' => $correctRequest->break2_in,
            'break2_out' => $correctRequest->break2_out,
            'remark' => $correctRequest->remark,
        ]);

        $correctRequest->update(['status' => 1]);

        return redirect()->route('admin.request.list')->with('success', '修正申請を承認しました');
    }

    /**
     * PG11: スタッフ別勤怠一覧画面（管理者）
     */
    public function staffAttendance(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($attendance) {
                return $this->calculateTotalTimes($attendance); // 計算メソッドを呼び出す
            });

        return view('admin.attendance.staff', [
            'staff' => $staff,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * 勤怠データの更新処理
     */
    public function update(Request $request, $id)
    {
        // 💡 管理者による修正時のバリデーションを要件（FN039）に完全一致させます
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

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'break_in' => $request->break_in,
            'break_out' => $request->break_out,
        ]);

        return redirect()->route('admin.attendance.detail', ['id' => $id])
            ->with('success', '勤怠データを修正しました');
    }

    private function calculateTotalTimes($attendance)
    {
        // 休憩1の分数
        $break1 = ($attendance->break_in && $attendance->break_out)
            ? Carbon::parse($attendance->break_in)->diffInMinutes(Carbon::parse($attendance->break_out)) : 0;

        // 休憩2の分数
        $break2 = ($attendance->break2_in && $attendance->break2_out)
            ? Carbon::parse($attendance->break2_in)->diffInMinutes(Carbon::parse($attendance->break2_out)) : 0;

        // 合計休憩時間
        $totalBreakMinutes = $break1 + $break2;

        // 💡 休憩データが全く無ければ「有」「00:00」ではなく空白にする要件に対応
        if ($totalBreakMinutes > 0) {
            $attendance->total_break = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
        } else {
            $attendance->total_break = '';
        }

        // 合計勤務時間 (退勤 - 出勤 - 休憩合計)
        if ($attendance->punch_in && $attendance->punch_out) {
            $workMinutes = Carbon::parse($attendance->punch_in)->diffInMinutes(Carbon::parse($attendance->punch_out));
            $actualWorkMinutes = max(0, $workMinutes - $totalBreakMinutes);
            $attendance->total_time = sprintf('%02d:%02d', floor($actualWorkMinutes / 60), $actualWorkMinutes % 60);
        } else {
            // 💡 ハイフン「-」ではなく要件通り「空白」にします
            $attendance->total_time = '';
        }

        return $attendance;
    }

    public function exportCsv($id)
    {
        $user = User::findOrFail($id);
        $attendances = Attendance::where('user_id', $id)->orderBy('date', 'desc')->get();

        $response = new StreamedResponse(function () use ($attendances, $user) {
            $handle = fopen('php://output', 'w');

            fputs($handle, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩合計', '勤務合計', '備考']);

            foreach ($attendances as $attendance) {
                $data = $this->calculateTotalTimes($attendance);

                fputcsv($handle, [
                    $attendance->date,
                    $attendance->punch_in,
                    $attendance->punch_out,
                    $data->total_break,
                    $data->total_time,
                    $attendance->remark
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $filename = $user->name . 'さんの勤怠一覧_' . date('Ymd') . '.csv';
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
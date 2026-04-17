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
     * 特定の勤怠データの詳細を表示し、修正・承認のベースとなります
     */
    public function show($id)
    {
        // 勤怠データを取得
        $attendance = Attendance::with('user')->findOrFail($id);

        // その勤怠に対して「承認待ち」の修正申請があるか確認
        $pendingRequest = \App\Models\AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 0)
            ->first();

        // 管理者用の詳細表示Viewを返す
        // ※View名はご自身の環境に合わせてください（例: admin.attendance.detail）
        return view('admin.attendance.detail', compact('attendance', 'pendingRequest'));
    }

    /**
     * PG10: 修正申請の承認処理
     */
    public function approve(Request $request, $attendance_correct_request_id)
    {
        // 1. 申請データを取得
        $correctRequest = \App\Models\AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        // 2. 反映先となる元の勤怠データを取得
        $attendance = Attendance::findOrFail($correctRequest->attendance_id);

        // 3. 勤怠データを申請内容で更新
        // ここでマイグレーション後の新カラム名（in/out）を正確に指定します
        $attendance->update([
            'punch_in' => $correctRequest->punch_in,
            'punch_out' => $correctRequest->punch_out,
            'break_in' => $correctRequest->break_in,
            'break_out' => $correctRequest->break_out,
            'break2_in' => $correctRequest->break2_in,
            'break2_out' => $correctRequest->break2_out,
            'remark' => $correctRequest->remark,
        ]);

        // 4. 申請ステータスを「1: 承認済み」に変更
        $correctRequest->update(['status' => 1]);

        // 5. 承認完了後、申請一覧画面へ戻る
        // ルート名は web.php の設定に合わせてください（例: admin.request.list）
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
            'currentMonth' => $currentMonth, // format('Y/m') を消して Carbonオブジェクトのまま渡す
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
    /**
     * 勤怠データの更新処理
     */
    public function update(Request $request, $id)
    {
        // 管理者による修正時のバリデーション
        $request->validate([
            'punch_in' => 'required',
            'punch_out' => 'required|after:punch_in',
            'break_in' => 'nullable|after:punch_in|before:punch_out',
            'break_out' => 'nullable|after:break_in|before:punch_out',
            'remark' => 'required|string|max:255',
        ], [
            'punch_out.after' => '出勤時刻より後の時刻を入力してください',
            'break_in.after' => '休憩開始は出勤時刻より後の時刻を入力してください',
            'break_in.before' => '休憩開始は退勤時刻より前の時刻を入力してください',
            'break_out.after' => '休憩終了は休憩開始より後の時刻を入力してください',
            'break_out.before' => '休憩終了は退勤時刻より前の時刻を入力してください',
            'remark.required' => '備考を記入してください',
        ]);

        // 保存処理（管理者なので申請を通さず直接上書き）
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'punch_in' => $request->punch_in,
            'punch_out' => $request->punch_out,
            'break_in' => $request->break_in,
            'break_out' => $request->break_out,
            // 管理者が修正した場合は備考をどう扱うか、必要に応じて更新
        ]);

        return redirect()->route('admin.attendance.detail', ['id' => $id])
            ->with('success', '勤怠データを修正しました');
    }
    private function calculateTotalTimes($attendance)
    {
        // 休憩1の分数 (break_start -> break_in に修正)
        $break1 = ($attendance->break_in && $attendance->break_out)
            ? Carbon::parse($attendance->break_in)->diffInMinutes(Carbon::parse($attendance->break_out)) : 0;

        // 休憩2の分数 (break2_start -> break2_in に修正)
        $break2 = ($attendance->break2_in && $attendance->break2_out)
            ? Carbon::parse($attendance->break2_in)->diffInMinutes(Carbon::parse($attendance->break2_out)) : 0;

        // 合計休憩時間
        $totalBreakMinutes = $break1 + $break2;
        $attendance->total_break = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        // 合計勤務時間 (退勤 - 出勤 - 休憩合計)
        if ($attendance->punch_in && $attendance->punch_out) {
            $workMinutes = Carbon::parse($attendance->punch_in)->diffInMinutes(Carbon::parse($attendance->punch_out));
            $actualWorkMinutes = max(0, $workMinutes - $totalBreakMinutes);
            $attendance->total_time = sprintf('%02d:%02d', floor($actualWorkMinutes / 60), $actualWorkMinutes % 60);
        } else {
            $attendance->total_time = '-';
        }

        return $attendance;
    }

    public function exportCsv($id)
    {
        $user = User::findOrFail($id);
        // そのユーザーの全勤怠データを取得（月などで絞り込む場合は適宜調整）
        $attendances = Attendance::where('user_id', $id)->orderBy('date', 'desc')->get();

        $response = new StreamedResponse(function () use ($attendances, $user) {
            $handle = fopen('php://output', 'w');

            // 文字化け防止（Excel用）
            fputs($handle, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩合計', '勤務合計', '備考']);

            foreach ($attendances as $attendance) {
                // 計算メソッドが既にある場合はそれを利用
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
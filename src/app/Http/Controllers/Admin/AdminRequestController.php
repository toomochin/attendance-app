<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance; // 実際には申請専用のテーブル(AttendanceCorrectRequest等)との紐付けが理想的です

class AdminRequestController extends Controller
{
    /**
     * PG12: 申請一覧画面（管理者）
     */
    public function index(Request $request)
    {
        // タブの状態（0:承認待ち, 1:承認済み）を取得。デフォルトは0。
        $status = $request->input('status', 0);

        // 修正申請テーブルからデータを取得し、関連するユーザーと打刻情報も一緒に読み込む
        $requests = \App\Models\AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.request.list', compact('requests', 'status'));
    }

    /**
     * PG13: 修正申請承認画面の表示
     */
    public function showApprove($attendance_correct_request_id)
    {
        $request = \App\Models\AttendanceCorrectRequest::with(['user', 'attendance'])->findOrFail($attendance_correct_request_id);
        return view('admin.request.approve', compact('request'));
    }

    /**
     * PG13: 修正申請の承認処理
     */
    public function approve($attendance_correct_request_id)
    {
        $correctRequest = \App\Models\AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

        // 1. 元の勤怠データ(Attendance)を申請内容で更新
        $attendance = \App\Models\Attendance::findOrFail($correctRequest->attendance_id);
        $attendance->update([
            'punch_in' => $correctRequest->punch_in,
            'punch_out' => $correctRequest->punch_out,
            'break_start' => $correctRequest->break_start,
            'break_end' => $correctRequest->break_end,
            'break2_start' => $correctRequest->break2_start,
            'break2_end' => $correctRequest->break2_end,
        ]);

        // 2. 申請ステータスを「承認済み(1)」に更新
        $correctRequest->update(['status' => 1]);

        return back()->with('success', '承認が完了しました');
    }
}
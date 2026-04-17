<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
| 一般ユーザー用ルート
*/
Route::middleware(['auth'])->group(function () {

    // PG03: 勤怠登録画面（トップページ）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/', function () {
        return redirect()->route('attendance.index');
    });

    // 打刻処理
    Route::post('/stamp', [AttendanceController::class, 'stamp'])->name('attendance.stamp');

    // PG04: 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // PG05: 勤怠詳細画面（要件通りのパス: /attendance/detail/{id}）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
| 管理者用ルート
*/
Route::prefix('admin')->group(function () {
    // PG07: 管理者ログイン画面
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:admin'])->group(function () {
        // PG08: 管理者用勤怠一覧画面
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

        // PG09: 管理者用勤怠詳細画面
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

        // PG10: スタッフ一覧画面
        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');

        // PG11: スタッフ別勤怠一覧画面
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin.attendance.staff');

        // PG13: 修正申請承認画面
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'showApprove'])->name('admin.request.approve');
        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminRequestController::class, 'approve']);

        // CSV出力
        Route::get('/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.export');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });
});

/*
| 共通ルート（PG06 / PG12: 申請一覧画面）
| 要件: 一般ユーザーと管理者で同じパスを使用する
*/
Route::middleware(['auth:web,admin'])->group(function () {
    // PG06 & PG12: 申請一覧画面
    Route::get('/stamp_correction_request/list', function () {
        if (Auth::guard('admin')->check()) {
            // 管理者の場合は管理者のコントローラーへ
            return app(AdminRequestController::class)->index(request());
        }
        // 一般ユーザーの場合は一般のコントローラーへ
        return app(AttendanceController::class)->requestList(request());
    })->name('request.list');
});
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    /**
     * PG10: スタッフ一覧画面（管理者）
     */
    public function index()
    {
        // roleが0（一般スタッフ）のユーザーを全て取得
        $staffs = User::where('role', 0)->get();

        return view('admin.staff.list', compact('staffs'));
    }
}
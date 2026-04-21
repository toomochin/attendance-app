<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceCorrectRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_correct_requests', function (Blueprint $table) {
            $table->id();// 修正対象となる勤怠レコードへのID
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');// 申請を行ったユーザーのID
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 出勤・退勤時刻
            $table->time('punch_in');
            $table->time('punch_out')->nullable();

            // 休憩1
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();

            // 休憩2
            $table->time('break2_in')->nullable();
            $table->time('break2_out')->nullable();

            // 修正理由
            $table->text('remark');

            // 承認ステータス（0: 承認待ち, 1: 承認済み）
            $table->integer('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_correct_requests');
    }
}
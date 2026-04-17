<?php  // ← ファイルの先頭はこれ1つだけにする

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');

            // カラム名が in/out で統一されているか確認
            $table->time('punch_in')->nullable();
            $table->time('punch_out')->nullable();
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();
            $table->time('break2_in')->nullable();
            $table->time('break2_out')->nullable();

            $table->text('remark')->nullable();
            $table->string('request_status')->nullable();
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
        Schema::dropIfExists('attendances');
    }
}
// ここより下に <?php や余計な文字がないことも確認
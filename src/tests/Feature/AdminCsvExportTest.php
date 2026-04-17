<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminCsvExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者はスタッフの勤怠データをCSV形式でダウンロードできる()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create(['role' => 0]);

        // テスト用ルートにアクセス
        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.export', ['id' => $user->id]));

        // レスポンスの確認
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        // ヘッダーに日付、出勤などの項目が含まれているか
        $this->assertStringContainsString('日付', $response->streamedContent());
    }
}
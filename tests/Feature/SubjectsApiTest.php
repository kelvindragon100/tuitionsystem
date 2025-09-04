<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(RefreshDatabase::class);

test('subjects api returns paginated subjects filtered by query with resource structure', function () {
    // 认证：任何已登录用户即可（只读 API）
    $user = User::factory()->create(['role' => 'tutor']);
    Sanctum::actingAs($user);

    // 插入若干科目（根据你的表结构：主键 subject_id，名称 subject_Name）
    DB::table('subjects')->insert([
        ['subject_id' => 'MATH01', 'subject_Name' => 'Mathematics'],
        ['subject_id' => 'PHY01',  'subject_Name' => 'Physics'],
        ['subject_id' => 'MATH02', 'subject_Name' => 'Further Mathematics'],
        ['subject_id' => 'CHEM01', 'subject_Name' => 'Chemistry'],
    ]);

    // 查询包含 math 的科目，分页 1/每页 2
    $res = $this->getJson('/api/subjects?q=math&per_page=2');

    $res->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['subject_id','subject_name']
            ],
            'links',
            'meta',
        ]);

    $data = $res->json('data');
    expect($data)->toBeArray()->and(count($data))->toBeLessThanOrEqual(2);

    foreach ($data as $row) {
        // 名称应包含 math（不区分大小写）
        expect(strtolower($row['subject_name']))->toContain('math');
    }

    $meta = $res->json('meta');
    expect($meta['per_page'])->toBe(2);
});

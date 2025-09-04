<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(RefreshDatabase::class);

test('users api returns paginated tutors filtered by search with resource structure', function () {
    // 认证：用 Sanctum 让一个管理员调用 API
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    // 准备数据：3 个 tutor（名字里含 Lee），2 个 student（名字里含 Lee），以及其它若干
    User::factory()->count(3)->create(['role' => 'tutor', 'name' => 'Lee Tutor', 'email' => fn() => fake()->unique()->safeEmail()]);
    User::factory()->count(2)->create(['role' => 'student', 'name' => 'Lee Student', 'email' => fn() => fake()->unique()->safeEmail()]);
    User::factory()->count(3)->create(['role' => 'tutor', 'name' => 'Alice', 'email' => fn() => fake()->unique()->safeEmail()]);

    // 请求：仅 tutor，搜索 Lee，限制 per_page=2（测试分页）
    $res = $this->getJson('/api/users?role=tutor&search=Lee&per_page=2');

    $res->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['id','name','email','role','status','created_at']
            ],
            'links',
            'meta',
        ]);

    // 每页最多 2 条
    $data = $res->json('data');
    expect($data)->toBeArray()->and(count($data))->toBeLessThanOrEqual(2);

    // 所有返回项都应为 tutor 且姓名包含 Lee（不区分大小写）
    foreach ($data as $row) {
        expect($row['role'])->toBe('tutor');
        expect(strtolower($row['name']))->toContain('lee');
        // Resource 字段存在且格式合理
        expect($row)->toHaveKeys(['id','name','email','status','created_at']);
    }

    // meta 里应包含分页信息
    $meta = $res->json('meta');
    expect($meta)->toHaveKeys(['current_page','per_page','total','last_page']);
    expect($meta['per_page'])->toBe(2);
});

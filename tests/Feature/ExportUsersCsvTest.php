<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

function stripBom(string $s): string {
    // 去掉 UTF-8 BOM，避免断言失败
    return preg_replace('/^\xEF\xBB\xBF/', '', $s);
}

test('admin can export tutors csv with search filter and correct headers', function () {
    // 1) 创建一个管理员并登录
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);
    $this->actingAs($admin);

    // 2) 造数据：3 个 tutor（含 “Lee”），2 个 student（含 “Lee”），以及其它 tutor
    User::factory()->create(['role' => 'tutor', 'name' => 'Lee Tutor 1', 'email' => 'lee.t1@example.com']);
    User::factory()->create(['role' => 'tutor', 'name' => 'Lee Tutor 2', 'email' => 'lee.t2@example.com']);
    User::factory()->create(['role' => 'tutor', 'name' => 'Bob Tutor',   'email' => 'bob.t@example.com']);

    User::factory()->create(['role' => 'student', 'name' => 'Lee Student 1', 'email' => 'lee.s1@example.com']);
    User::factory()->create(['role' => 'student', 'name' => 'Lee Student 2', 'email' => 'lee.s2@example.com']);

    // 3) 发起导出请求：只导出 tutor，且搜索关键字 Lee
    $url = route('admin.users.export', ['type' => 'tutor', 'search' => 'Lee']);
    $response = $this->get($url);

    // 4) 断言响应头
    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $this->assertTrue(
        str_contains($response->headers->get('Content-Disposition'), 'attachment')
        && str_contains(strtolower($response->headers->get('Content-Disposition')), '.csv'),
        'Content-Disposition 应为附件且带 .csv 文件名'
    );

    // 5) 读取 CSV 内容（注意：导出使用了 StreamedResponse，要用 streamedContent）
    $content = method_exists($response, 'streamedContent')
        ? $response->streamedContent()
        : $response->getContent(); // 兼容性fallback

    $content = stripBom($content);

    // 第一行应为表头
    $lines = preg_split("/\r\n|\n|\r/", $content);
    $header = trim($lines[0] ?? '');
    expect($header)->toBe('ID,Name,Email,Role,Status,Created At');

    // 6) 内容应包含 Lee Tutor 的记录
    expect($content)->toContain('Lee Tutor 1');
    expect($content)->toContain('Lee Tutor 2');

    // 7) 不应包含 Lee Student（因为 type=tutor）
    expect($content)->not()->toContain('Lee Student 1');
    expect($content)->not()->toContain('Lee Student 2');

    // 8) 可选：也不应包含非 Lee 的 tutor（因为 search=Lee）
    expect($content)->not()->toContain('Bob Tutor');
});

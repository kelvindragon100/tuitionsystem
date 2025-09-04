<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

uses(RefreshDatabase::class);

/** 辅助：读取封禁状态（兼容 banned_at / is_banned） */
function _isBanned(User $u): bool {
    $attrs = $u->getAttributes();
    if (array_key_exists('banned_at', $attrs)) {
        return !is_null($u->banned_at);
    }
    if (array_key_exists('is_banned', $attrs)) {
        return (bool) $u->is_banned;
    }
    return false;
}

/** 辅助：断言数据库中用户未被封禁 */
function _assertNotBanned(int $id): void {
    $fresh = User::findOrFail($id);
    expect(_isBanned($fresh))->toBeFalse();
}

test('guest cannot ban user (redirects to login and target remains not banned)', function () {
    // 目标用户（tutor）
    $target = User::factory()->create(['role' => 'tutor']);

    // 未登录直接请求 Ban 路由
    $response = $this->patch(route('admin.users.ban', ['user' => $target->id]));

    // 对 guest，通常会重定向到登录页
    $response->assertRedirect(route('login'));
    _assertNotBanned($target->id);
});

test('non-admin authenticated user cannot ban user (forbidden or redirected) and target remains not banned', function () {
    // 登录一个非 admin（例如 tutor）
    $tutor = User::factory()->create(['role' => 'tutor']);
    $this->actingAs($tutor);

    // 目标用户（student）
    $target = User::factory()->create(['role' => 'student']);

    $response = $this->patch(route('admin.users.ban', ['user' => $target->id]));

    // 你的 isAdmin 中间件可能 403 或重定向，这里做宽容断言：
    expect(in_array($response->getStatusCode(), [302, 301, 403]))->toBeTrue();

    // 无论如何，目标仍未被封禁
    _assertNotBanned($target->id);
});

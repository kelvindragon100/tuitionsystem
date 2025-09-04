<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

uses(RefreshDatabase::class);

/**
 * 辅助：读取用户当前封禁状态（兼容 banned_at 或 is_banned）
 */
function userIsBanned(User $u): bool {
    $attrs = $u->getAttributes();
    if (array_key_exists('banned_at', $attrs)) {
        return !is_null($u->banned_at);
    }
    if (array_key_exists('is_banned', $attrs)) {
        return (bool) $u->is_banned;
    }
    return false;
}

/**
 * 辅助：断言数据库中的封禁状态（重新从数据库取一遍）
 */
function assertUserBannedFlag(int $id, bool $expected): void {
    $fresh = User::findOrFail($id);
    expect(userIsBanned($fresh))->toBe($expected);
}

test('admin can ban a tutor and the tutor is blocked by middleware', function () {
    // 1) Admin 登录
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // 2) 目标用户（tutor）
    $tutor = User::factory()->create(['role' => 'tutor']);

    // 3) 调用封禁路由
    $res = $this->patch(route('admin.users.ban', ['user' => $tutor->id]));
    $res->assertRedirect(); // 控制器通常 redirect()->back()
    assertUserBannedFlag($tutor->id, true);

    // 4) 被封禁用户尝试访问受保护路由：应被踢回登录页
    $this->flushSession(); // 清理当前会话
    $this->actingAs(User::find($tutor->id)); // 以被封禁用户身份登录
    $resp = $this->get('/dashboard');
    $resp->assertRedirect(route('login'));
    $this->assertGuest(); // 中间件应已执行 logout()
});

test('admin can unban a tutor and the tutor can access dashboard again', function () {
    // 1) Admin 登录
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    // 2) 先创建一个已被封禁的 tutor
    $tutor = User::factory()->create(['role' => 'tutor']);
    if (Schema::hasColumn('users', 'banned_at')) {
        $tutor->forceFill(['banned_at' => now()])->save();
    } else {
        $tutor->forceFill(['is_banned' => true])->save();
    }
    assertUserBannedFlag($tutor->id, true);

    // 3) 调用解封路由
    $res = $this->patch(route('admin.users.unban', ['user' => $tutor->id]));
    $res->assertRedirect();
    assertUserBannedFlag($tutor->id, false);

    // 4) 解封后再以该用户访问受保护路由：应不再被踢回登录
    $this->flushSession();
    $this->actingAs(User::find($tutor->id));
    $resp = $this->get('/dashboard');

    // 你的 /dashboard 会根据角色重定向到 role 对应的 dashboard
    $resp->assertRedirect(route('tutor.dashboard'));
});

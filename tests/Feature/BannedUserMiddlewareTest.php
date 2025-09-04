<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

uses(RefreshDatabase::class);

test('banned user cannot access protected routes and is redirected to login', function () {
    // 创建一个被封禁的用户：优先使用 banned_at；若无此列则回退 is_banned
    $user = User::factory()->create();

    if (Schema::hasColumn('users', 'banned_at')) {
        $user->forceFill(['banned_at' => now()])->save();
    } else {
        $user->forceFill(['is_banned' => true])->save();
    }

    // 登录并访问受保护路由（/dashboard 已挂载了 auth + notBanned）
    $this->actingAs($user);
    $response = $this->get('/dashboard');

    // 应该被登出并重定向到登录页（EnsureNotBanned 的行为）
    $response->assertRedirect(route('login'));
    $this->assertGuest(); // 当前会话应为未登录
});

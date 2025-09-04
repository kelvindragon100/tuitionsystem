<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 登录兜底限流（较宽松）：用于洪水防护/DoS 抑制
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                // 邮箱+IP：每分钟 30 次（兜底，须宽松于控制器的 4/120s）
                Limit::perMinute(30)->by($email.'|'.$request->ip()),

                // 仅 IP：每分钟 50 次，限制单 IP 爆刷
                Limit::perMinute(50)->by('ip:'.$request->ip()),
            ];
        });
    }
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',      // 👈 加上这一行
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 中间件别名（Laravel 11+）
        $middleware->alias([
            'isAdmin'   => \App\Http\Middleware\IsAdmin::class,
            'notBanned' => \App\Http\Middleware\EnsureNotBanned::class,
        ]);

        // ⚠️ 一般不要重写 web 组，避免漏掉 CSRF/Session。
        // 如需自定义，请确保包含 VerifyCsrfToken 和 StartSession。
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ✅ 统一的 CSRF 失配处理（绿色盾牌）
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // API/JSON 请求 → 419 JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }

            // 仅对可能改写数据的方法回退
            if ($request->isMethod('post') || $request->isMethod('put') ||
                $request->isMethod('patch') || $request->isMethod('delete')) {

                $fallback = url()->previous() ?: route('login');

                return redirect($fallback)
                    ->withInput($request->except('_token', 'password', 'password_confirmation'))
                    ->with('csrf_defended', true);   // 👈 前端就用这个 session key 显示绿色盾牌
            }

            // 其它情况交给框架默认处理
            return null;
        });
    })
    ->create();

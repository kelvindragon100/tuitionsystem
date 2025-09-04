<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 显示登录页
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * 登录处理（带暴力破解防护：同邮箱+IP 每分钟 4次）
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $maxAttempts  = 4;    // 每分钟最大尝试次
        $decaySeconds = 60;   // 冷却窗口(秒)
        $key = $this->throttleKey($request); // email|ip

        // 已超限：直接提示冷却秒数
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // 回传给前端：冷却秒数
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('auth.throttle', ['seconds' => $seconds])]) // "Too many attempts. Try again in :seconds seconds."
                ->with('login_cooldown', $seconds);
        }

        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // 登录失败：命中一次，计算剩余
            RateLimiter::hit($key, $decaySeconds);
            $remaining = RateLimiter::remaining($key, $maxAttempts); // Laravel 提供

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => __('auth.failed')]) // 统一错误，防账号枚举
                ->with('login_attempts', [
                    'remaining' => max(0, $remaining),
                    'max'       => $maxAttempts,
                    'window'    => $decaySeconds,
                ]);
        }

        // 成功：清计数、刷新会话、分流
        RateLimiter::clear($key);

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $user = $request->user();
        return $user->role === 'admin'  ? redirect()->route('admin.dashboard')
            : ($user->role === 'tutor' ? redirect()->route('tutor.dashboard')
                                        : redirect()->route('student.dashboard'));
    }

    /**
     * 退出登录
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * 生成限流 Key：邮箱(小写)+IP 组合
     */
    protected function throttleKey(Request $request): string
    {
        $email = strtolower((string) $request->input('email'));
        return 'login:'.$email.'|'.$request->ip();
    }
}

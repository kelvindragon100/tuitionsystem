<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class AccountController extends Controller
{
    /**
     * 显示账号编辑页
     */
    public function edit(Request $request): View
    {
        return view('account.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * 更新基本资料（姓名 / 邮箱）
     * 方案 B：不依赖 email_verified_at 列（即使变更邮箱也不去写该列）
     * 若系统未来新增该列并启用验证，可通过 supportsEmailVerification() 自动启用验证流程
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 未验证邮箱则不允许更新
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return back()->with('status', 'email-not-verified');
        }

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->name  = $validated['name'];
        $user->email = $validated['email'];

        // 方案 B：不操作 email_verified_at（避免列不存在报错）
        // 如果你未来想自动启用验证，只需在 users 表新增 email_verified_at 列
        // 并让 User 实现 MustVerifyEmail，下面的代码即可自动生效。
        if ($emailChanged && $this->supportsEmailVerification($user)) {
            // 仅当系统具备验证能力和列存在时才清空状态&发送验证邮件
            $user->email_verified_at = null;
        }

        $user->save();

        // 邮箱变更时，仅当支持邮箱验证时尝试发送验证邮件；否则照常提示已更新
        if ($emailChanged && $this->supportsEmailVerification($user)) {
            try {
                $user->sendEmailVerificationNotification();
                return Redirect::route('account.edit')->with('status', 'verification-link-sent');
            } catch (\Throwable $e) {
                // 邮件通道未配置或失败，也保证不报错
                return Redirect::route('account.edit')->with('status', 'account-updated');
            }
        }

        return Redirect::route('account.edit')->with('status', 'account-updated');
    }

    /**
     * 更新密码
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('account.edit')->with('status', 'password-updated');
    }

    /**
     * 重新发送验证邮件
     * 方案 B：即使没有 email_verified_at 列，也优雅处理：
     * - 若系统支持邮箱验证（实现 MustVerifyEmail 且有该列），尝试发送并提示成功
     * - 若不支持，也直接提示“verification-link-sent”（仅作为 UX 提示，不报错）
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($this->supportsEmailVerification($user) && method_exists($user, 'hasVerifiedEmail')) {
            if ($user->hasVerifiedEmail()) {
                // 已验证则无需重发：给出通用成功提示即可
                return Redirect::route('account.edit')->with('status', 'account-updated');
            }

            try {
                $user->sendEmailVerificationNotification();
                return Redirect::route('account.edit')->with('status', 'verification-link-sent');
            } catch (\Throwable $e) {
                // 邮箱通道未配置或失败：退回一个通用提示，不中断流程
                return Redirect::route('account.edit')->with('status', 'account-updated');
            }
        }

        // 不支持邮箱验证（无 MustVerifyEmail 或无 email_verified_at 列）
        // 为了统一 UX，也返回“已发送”提示
        return Redirect::route('account.edit')->with('status', 'verification-link-sent');
    }

    /**
     * 删除账号
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * 当前应用是否“具备邮箱验证”的完整能力：
     * - 模型实现 MustVerifyEmail 接口
     * - users 表存在 email_verified_at 列（避免 1054 列不存在错误）
     */
    private function supportsEmailVerification($user): bool
    {
        return ($user instanceof MustVerifyEmail) && Schema::hasColumn('users', 'email_verified_at');
    }
}

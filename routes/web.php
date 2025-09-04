<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubjectController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Dashboard 跳转（按角色）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'notBanned'])->get('/dashboard', function () {
    $user = Auth::user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'tutor') {
        return redirect()->route('tutor.dashboard');
    } else {
        return redirect()->route('student.dashboard');
    }
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Account（个人账户）+ 邮箱验证相关
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'notBanned'])->group(function () {
    // 账户编辑/更新/删除
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');

    // 重新发送验证邮件
    Route::post('/email/verification-notification', [AccountController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 验证提示页（未验证用户访问受保护页面时跳转到此）
    Route::get('/email/verify', function () {
        return view('auth.verify-email');   // 需要存在 resources/views/auth/verify-email.blade.php
    })->name('verification.notice');

    // 验证回调（用户点击邮件中的链接后到此）
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill(); // 标记邮箱为已验证（写入 email_verified_at）
        return redirect()->route('account.edit')->with('status', 'email-verified');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Admin routes（/admin 前缀 + auth + isAdmin + notBanned）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'isAdmin', 'notBanned'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // Users 管理
        Route::get('/users', [AdminController::class, 'manageUsers'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUserForm'])->name('users.create');
        Route::post('/users/create', [AdminController::class, 'createUser']);
        Route::get('/users/{id}/edit', [AdminController::class, 'editUserForm'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // 导出 CSV（带上当前筛选条件：type/search）+ 轻度限流
        Route::get('/users/export', [AdminController::class, 'export'])
            ->middleware('throttle:3,1')
            ->name('users.export');

        // 封禁 / 解封（Route Model Binding: {user}）
        Route::patch('/users/{user}/ban',   [AdminController::class, 'ban'])->name('users.ban');
        Route::patch('/users/{user}/unban', [AdminController::class, 'unban'])->name('users.unban');

        // 重置用户密码
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])
            ->name('users.resetPassword');

        // Subjects CRUD（基于主键 subject_id 进行模型绑定）
        Route::get('/subjects',                           [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create',                    [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects',                          [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject:subject_id}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject:subject_id}',      [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject:subject_id}',   [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });

/*
|--------------------------------------------------------------------------
| Tutor & Student dashboards
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'notBanned'])->group(function () {
    Route::get('/tutor/dashboard', function () {
        return view('tutor.dashboard');
    })->name('tutor.dashboard');

    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
});

// 示例页
Route::get('/test', function () {
    return view('test');
});

Route::get('/_mail_test', function () {
    try {
        Mail::raw('This is a raw test email body.', function ($message) {
            $message->to('你的接收邮箱@example.com') // 换成你要收的邮箱
                    ->subject('SMTP Test from Laravel');
        });
        return 'Mail sent OK. Check your inbox (and spam).';
    } catch (\Throwable $e) {
        Log::error('Mail test failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return 'Mail failed: '.$e->getMessage();
    }
});

// API 文档页（仅登录用户可看）
Route::middleware(['auth'])->get('/api-docs', function () {
    return view('api.docs');
})->name('api.docs');

// 生成个人访问令牌（仅开发演示用途）
// 提交后把 token 放到 session('api_token')，页面会显示出来
Route::middleware(['auth'])->post('/api-docs/token', function (\Illuminate\Http\Request $request) {
    $token = $request->user()->createToken('demo-token')->plainTextToken;
    return back()->with('api_token', $token);
})->name('api.docs.token');


// Breeze/Fortify/Auth 路由
require __DIR__ . '/auth.php';

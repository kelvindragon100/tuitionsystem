<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SubjectApiController;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:60,1']) // 如果 Swagger 没带 Token 会 401，调试期可临时注释
    ->group(function () {
        // Users
        Route::get('/users',      [UserApiController::class, 'index']);
        Route::get('/users/{id}', [UserApiController::class, 'show']);

        // Subjects（隐式绑定，{subject} 会按模型的 getRouteKeyName() = subject_id 解析）
        Route::get('/subjects',            [SubjectApiController::class, 'index']);
        Route::get('/subjects/{subject}',  [SubjectApiController::class, 'show']);
    });

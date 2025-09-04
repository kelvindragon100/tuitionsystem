<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    // 查看单个 Subject
    public function view(User $user, Subject $subject): bool
    {
        if ($user->role === 'admin') return true;

        if ($user->role === 'student') {
            // 学生只能访问自己已选的科目
            // 依据你当前关系命名调整：subjects 或 enrollments → subjects
            return $user->subjects()->whereKey($subject->getKey())->exists();
        }

        if ($user->role === 'tutor') {
            // 导师只能访问自己任教的科目
            return (int)$subject->tutor_id === (int)$user->getKey();
        }

        return false;
    }

    // 根据需要可扩展 update/delete 等
    public function update(User $user, Subject $subject): bool
    {
        return $user->role === 'admin' || ( $user->role === 'tutor' && (int)$subject->tutor_id === (int)$user->id );
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->role === 'admin';
    }
}

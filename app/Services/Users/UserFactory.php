<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Users\Contracts\UserCreator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class UserFactory implements UserCreator
{
    public function create(array $data): User
    {
        $role     = strtolower($data['role'] ?? 'student');
        $name     = $data['name'] ?? 'User';
        $email    = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email) {
            throw new \InvalidArgumentException('Email is required.');
        }

        // 基本字段
        $user = new User();
        $user->role     = in_array($role, ['admin','tutor','student']) ? $role : 'student';
        $user->name     = $name;
        $user->email    = $email;
        $user->password = $password ? Hash::make($password) : Hash::make(static::generateStrongPassword());

        // 各角色可以在此设置默认值（例如 profile 字段/状态等）
        // if ($user->role === 'tutor') { ... }
        // if ($user->role === 'student') { ... }

        $user->save();

        // 学生：同步科目（多对多）
        if ($user->role === 'student') {
            $subjectIds = Arr::wrap($data['subjects'] ?? []);
            if (!empty($subjectIds)) {
                $user->subjects()->sync($subjectIds);
            }
        }

        return $user;
    }

    /** 生成强密码 */
    public static function generateStrongPassword(int $length = 12): string
    {
        $length  = max($length, 8);
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghijkmnpqrstuvwxyz';
        $digits  = '23456789';
        $symbols = '!@#$%^&*()-_=+[]{}<>?';

        $password = [
            $upper[random_int(0, strlen($upper)-1)],
            $lower[random_int(0, strlen($lower)-1)],
            $digits[random_int(0, strlen($digits)-1)],
            $symbols[random_int(0, strlen($symbols)-1)],
        ];

        $all = $upper.$lower.$digits.$symbols;
        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all)-1)];
        }
        for ($i = count($password) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
        }
        return implode('', $password);
    }
}

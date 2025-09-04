<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ✅ 用于 createToken()

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; // ✅ 接入 Sanctum

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // 可选：若你在某些地方用 mass assignment 创建/更新角色或封禁字段，放开它们更方便：
        'role',       // 'admin' | 'tutor' | 'student'
        'banned_at',  // 时间戳式封禁字段（如果你表里有）
        // 'is_banned', // 如果你用的是布尔字段而不是时间戳，可按需放开
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            // 便于以 Carbon 使用（如果存在该列）
            'banned_at'         => 'datetime',
        ];
    }

    /**
     * 学科关联（学生-科目 多对多）
     */
    public function subjects()
    {
        return $this->belongsToMany(
            \App\Models\Subject::class,
            'student_subject',  // pivot 表
            'student_id',       // 当前模型在 pivot 的外键
            'subject_id',       // 目标模型在 pivot 的外键
            'id',               // 当前模型主键
            'subject_id'        // 目标模型主键
        );
    }

    /**
     * 统一的封禁状态访问器：
     * - 若存在 banned_at（时间戳）则以是否为 null 判断
     * - 否则若存在 is_banned（布尔）则用其值
     * - 两者都无则返回 false
     */
    public function getIsBannedAttribute(): bool
    {
        if (array_key_exists('banned_at', $this->attributes)) {
            return !is_null($this->banned_at);
        }
        if (array_key_exists('is_banned', $this->attributes)) {
            return (bool) $this->attributes['is_banned'];
        }
        return false;
    }
}

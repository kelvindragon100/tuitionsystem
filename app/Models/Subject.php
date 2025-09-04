<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasPrefixedId;

class Subject extends Model
{
    use HasPrefixedId;

    protected $table = 'subjects';          // 若表名是 subjects，显式声明更清晰
    protected $primaryKey = 'subject_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const ID_PREFIX = 'SU';
    public const ID_PAD_LENGTH = 4;

    protected $fillable = [
        'subject_Name',
        'subject_Description',
        'duration_Hours',
        'subject_Fee',
    ];

    protected $casts = [
        'duration_Hours' => 'integer',
        'subject_Fee'    => 'decimal:2',    // 数据库存储 decimal 时输出更稳定
    ];

    public function classes()
    {
        return $this->hasMany(Classes::class, 'subject_id', 'subject_id');
    }

    public function materials()
    {
        return $this->hasMany(Materials::class, 'subject_id', 'subject_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_subject', 'subject_id', 'student_id');
    }

    public function getRouteKeyName()
    {
        return 'subject_id'; // 让 {subject} 绑定用 subject_id
    }
}

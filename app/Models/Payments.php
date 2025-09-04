<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasPrefixedId;

class Payments extends Model
{
    use HasPrefixedId;

    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public const ID_PREFIX = 'P';
    public const ID_PAD_LENGTH = 4;

    protected $fillable = [
        'student_id', 'paymentTotal', 'paymentDate', 'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'payment_id', 'payment_id');
    }
}






<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'created_at' => optional($this->created_at)->toIso8601String(),
            // subjects 在 show() 里才会预加载，没加载时就是 []
            'subjects'   => $this->whenLoaded('subjects', function () {
                return $this->subjects->map(fn($s) => [
                    'subject_id'   => $s->subject_id,
                    'subject_Name' => $s->subject_Name,
                ]);
            }, []),
        ];
    }
}

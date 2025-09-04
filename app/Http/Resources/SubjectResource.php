<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Subject */
class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'subject_id'          => (string) $this->subject_id,
            'subject_Name'        => (string) $this->subject_Name,
            'subject_Description' => (string) ($this->subject_Description ?? ''),
            'duration_Hours'      => is_null($this->duration_Hours) ? null : (int) $this->duration_Hours,
            'subject_Fee'         => is_null($this->subject_Fee) ? null : (float) $this->subject_Fee,
            'created_at'          => optional($this->created_at)->toIso8601String(),
        ];
    }
}

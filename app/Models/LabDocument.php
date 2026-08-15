<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid', 'lab_id', 'title', 'type', 'file_path', 'disk', 'uploaded_by',
])]
class LabDocument extends Model
{
    use HasUuid;

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

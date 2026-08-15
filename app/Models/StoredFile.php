<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'checksum',
    'attachable_type', 'attachable_id', 'uploaded_by', 'visibility', 'meta',
])]
class StoredFile extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'meta' => 'array',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

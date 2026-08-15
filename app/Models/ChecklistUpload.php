<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid', 'document_checklist_id', 'item_key', 'original_name', 'file_path',
    'disk', 'uploaded_by_name', 'uploaded_by_email',
])]
class ChecklistUpload extends Model
{
    use HasUuid;

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(DocumentChecklist::class, 'document_checklist_id');
    }
}

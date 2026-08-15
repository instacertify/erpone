<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'project_id', 'assignee_id', 'title', 'description', 'status',
    'priority', 'due_date', 'estimate_hours', 'logged_hours', 'sort_order',
])]
class ProjectTask extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'estimate_hours' => 'integer',
            'logged_hours' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'code', 'name', 'customer_id', 'project_id', 'owner_id', 'status',
    'scope', 'planned_start', 'planned_end',
])]
class TestPlan extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'planned_start' => 'date',
            'planned_end' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(TestCase::class);
    }
}

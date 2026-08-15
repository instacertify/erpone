<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'test_plan_id', 'title', 'steps', 'expected_result', 'priority', 'status',
])]
class TestCase extends Model
{
    use HasUuid, SoftDeletes;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TestPlan::class, 'test_plan_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(TestRun::class);
    }
}

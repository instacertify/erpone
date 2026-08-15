<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'sample_id', 'customer_id', 'project_id', 'name', 'type', 'status',
    'received_at', 'due_at', 'storage_location', 'custodian_id', 'notes', 'meta',
])]
class Sample extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'due_at' => 'date',
            'meta' => 'array',
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

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }
}

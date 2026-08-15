<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'sample_id', 'qr_code', 'share_token', 'customer_id', 'project_id',
    'quotation_id', 'lab_id', 'name', 'type', 'status', 'tracking_status',
    'received_at', 'dispatched_at', 'testing_started_at', 'report_available_at',
    'report_uploaded_at', 'report_path', 'due_at', 'storage_location',
    'custodian_id', 'notes', 'meta',
])]
class Sample extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'dispatched_at' => 'datetime',
            'testing_started_at' => 'datetime',
            'report_available_at' => 'datetime',
            'report_uploaded_at' => 'datetime',
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

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }
}

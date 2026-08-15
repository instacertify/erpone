<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'title', 'person_name', 'company_name', 'request_type', 'company_size',
    'country', 'state', 'contact_number', 'customer_id', 'contact_id', 'owner_id',
    'consultant_id', 'source', 'lead_source', 'stage', 'estimated_value', 'currency',
    'expected_close_date', 'expected_timeline', 'probability', 'notes',
    'company_address', 'gst_details', 'meta',
])]
class Lead extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'expected_close_date' => 'date',
            'probability' => 'integer',
            'meta' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(Consultant::class);
    }
}

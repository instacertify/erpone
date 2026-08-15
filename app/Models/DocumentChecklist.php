<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid', 'service_id', 'quotation_id', 'customer_id', 'name', 'share_token',
    'includes_test_request_form', 'status', 'items',
])]
class DocumentChecklist extends Model
{
    use HasUuid;

    protected function casts(): array
    {
        return [
            'includes_test_request_form' => 'boolean',
            'items' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(ChecklistUpload::class);
    }
}

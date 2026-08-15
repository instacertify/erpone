<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'code', 'name', 'location', 'city', 'state', 'country', 'accreditation',
    'accreditation_number', 'scope_summary', 'contact_email', 'contact_phone',
    'is_active', 'price_sheet', 'meta',
])]
class Lab extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_sheet' => 'array',
            'meta' => 'array',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LabDocument::class);
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'code', 'name', 'category', 'description', 'default_timeline_days',
    'is_active', 'meta',
])]
class ServiceCatalog extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'services';

    protected function casts(): array
    {
        return [
            'default_timeline_days' => 'integer',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function quotationTemplates(): HasMany
    {
        return $this->hasMany(QuotationTemplate::class, 'service_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'service_id');
    }

    public function documentChecklists(): HasMany
    {
        return $this->hasMany(DocumentChecklist::class, 'service_id');
    }
}

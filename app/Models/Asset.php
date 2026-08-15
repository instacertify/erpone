<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'asset_code', 'name', 'category', 'value', 'currency', 'assigned_to',
    'registered_by', 'acquired_on', 'status', 'notes',
])]
class Asset extends Model
{
    use HasUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $asset): void {
            if (! empty($asset->asset_code)) {
                return;
            }

            do {
                $assetCode = 'AST-'.Str::upper(Str::random(4));
            } while (self::withTrashed()->where('asset_code', $assetCode)->exists());

            $asset->asset_code = $assetCode;
        });
    }

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'acquired_on' => 'date',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}

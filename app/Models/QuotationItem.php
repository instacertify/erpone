<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'quotation_id', 'description', 'item_type', 'pay_to', 'counts_as_revenue',
    'currency', 'quantity', 'samples_count', 'lab_id', 'applicable_standard',
    'lab_accreditation', 'testing_timeline', 'tests_required', 'unit_price',
    'tax_rate', 'line_total', 'sort_order',
])]
class QuotationItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            if (in_array($item->item_type, ['consulting', 'testing', 'lab'], true)) {
                $item->counts_as_revenue = true;
            } elseif ($item->item_type === 'government_fee') {
                $item->counts_as_revenue = false;
            }
            $item->line_total = round(
                (float) $item->quantity
                * (float) $item->unit_price
                * (1 + ((float) $item->tax_rate / 100)),
                2,
            );
        });

        static::saved(fn (self $item) => $item->quotation?->recalculateRevenueTotals());
        static::deleted(fn (self $item) => $item->quotation?->recalculateRevenueTotals());
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'samples_count' => 'integer',
            'counts_as_revenue' => 'boolean',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid', 'share_token', 'barcode', 'number', 'customer_id', 'lead_id', 'project_id',
    'created_by', 'assigned_to', 'status', 'category', 'service_id', 'template_id',
    'quote_date', 'valid_until', 'currency', 'exchange_rate', 'subtotal', 'tax_total',
    'total', 'total_inr', 'consulting_revenue', 'lab_revenue', 'passthrough_total',
    'notes', 'certification_timeline', 'terms_and_conditions', 'force_majeure',
    'customer_remarks', 'shared_at', 'accepted_at', 'rejected_at', 'meta',
])]
class Quotation extends Model
{
    use HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'valid_until' => 'date',
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'total_inr' => 'decimal:2',
            'consulting_revenue' => 'decimal:2',
            'lab_revenue' => 'decimal:2',
            'passthrough_total' => 'decimal:2',
            'shared_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuotationTemplate::class, 'template_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function recalculateRevenueTotals(): self
    {
        $items = $this->items()->get();

        $revenueItems = $items->where('counts_as_revenue', true);
        $labItemTypes = ['lab', 'testing'];
        $subtotal = $items->sum(
            fn (QuotationItem $item): float => (float) $item->quantity * (float) $item->unit_price,
        );
        $total = (float) $items->sum('line_total');

        $this->forceFill([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($total - $subtotal, 2),
            'total' => round($total, 2),
            'total_inr' => $this->currency === 'INR'
                ? round($total, 2)
                : round($total * (float) $this->exchange_rate, 2),
            'consulting_revenue' => $revenueItems
                ->reject(fn (QuotationItem $item): bool => in_array($item->item_type, $labItemTypes, true))
                ->sum('line_total'),
            'lab_revenue' => $revenueItems
                ->filter(fn (QuotationItem $item): bool => in_array($item->item_type, $labItemTypes, true))
                ->sum('line_total'),
            'passthrough_total' => $items
                ->where('counts_as_revenue', false)
                ->sum('line_total'),
        ])->save();

        return $this;
    }
}

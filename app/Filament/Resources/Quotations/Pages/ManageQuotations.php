<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationTemplate;
use App\Models\User;
use App\Services\Settings\ErpSettings;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;

class ManageQuotations extends ManageRecords
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    if (($data['currency'] ?? 'INR') !== 'INR') {
                        $rate = (float) ($data['exchange_rate'] ?? app(ErpSettings::class)->get('usd_to_inr_rate', 83.5));
                        $data['exchange_rate'] = $rate;
                        $data['total_inr'] = round(((float) ($data['total'] ?? 0)) * $rate, 2);
                    } else {
                        $data['exchange_rate'] = 1;
                        $data['total_inr'] = $data['total'] ?? 0;
                    }

                    return $data;
                })
                ->after(fn (Quotation $record) => $record->recalculateRevenueTotals()),
            Action::make('shareQuote')
                ->label('Share quote')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->schema([
                    Select::make('quotation_id')
                        ->label('Quotation')
                        ->options(fn () => QuotationResource::getEloquentQuery()
                            ->whereNotIn('status', ['accepted', 'rejected', 'expired'])
                            ->orderByDesc('quote_date')
                            ->pluck('number', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $quotation = QuotationResource::getEloquentQuery()->findOrFail($data['quotation_id']);
                    $token = $quotation->share_token ?: Str::random(48);
                    $barcode = $quotation->barcode ?: app(\App\Support\QrCodeService::class)->makeToken('QTE');

                    $quotation->update([
                        'share_token' => $token,
                        'barcode' => $barcode,
                        'status' => 'sent',
                        'shared_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Quotation ready to share')
                        ->body(url('/quote/'.$token))
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Action::make('saveAsTemplate')
                ->label('Save as template')
                ->icon('heroicon-o-document-duplicate')
                ->schema([
                    Select::make('quotation_id')
                        ->label('Quotation')
                        ->options(fn () => QuotationResource::getEloquentQuery()
                            ->orderByDesc('quote_date')
                            ->pluck('number', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('name')->required()->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $quotation = QuotationResource::getEloquentQuery()
                        ->with('items')
                        ->findOrFail($data['quotation_id']);

                    QuotationTemplate::create([
                        'name' => $data['name'],
                        'category' => $quotation->category,
                        'service_id' => $quotation->service_id,
                        'created_by' => auth()->id(),
                        'currency' => $quotation->currency,
                        'terms_and_conditions' => $quotation->terms_and_conditions,
                        'force_majeure' => $quotation->force_majeure,
                        'certification_timeline' => $quotation->certification_timeline,
                        'is_active' => true,
                        'items_payload' => $quotation->items
                            ->map(fn ($item): array => $item->only([
                                'description', 'item_type', 'pay_to', 'counts_as_revenue',
                                'currency', 'quantity', 'samples_count', 'lab_id',
                                'applicable_standard', 'lab_accreditation', 'testing_timeline',
                                'tests_required', 'unit_price', 'tax_rate', 'line_total', 'sort_order',
                            ]))
                            ->values()
                            ->all(),
                    ]);

                    Notification::make()->title('Quotation template created')->success()->send();
                }),
            Action::make('startProject')
                ->label('Start project')
                ->icon('heroicon-o-folder-plus')
                ->color('success')
                ->schema([
                    Select::make('quotation_id')
                        ->label('Accepted quotation')
                        ->options(fn () => QuotationResource::getEloquentQuery()
                            ->where('status', 'accepted')
                            ->whereNull('project_id')
                            ->orderByDesc('accepted_at')
                            ->pluck('number', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('code')->required()->maxLength(50)->unique('projects', 'code'),
                    TextInput::make('name')->required()->maxLength(255),
                    Select::make('manager_id')
                        ->options(fn () => User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    $quotation = QuotationResource::getEloquentQuery()
                        ->where('status', 'accepted')
                        ->whereNull('project_id')
                        ->findOrFail($data['quotation_id']);

                    $project = Project::create([
                        'code' => $data['code'],
                        'name' => $data['name'],
                        'customer_id' => $quotation->customer_id,
                        'quotation_id' => $quotation->id,
                        'manager_id' => $data['manager_id'] ?? null,
                        'sales_owner_id' => $quotation->assigned_to ?? $quotation->created_by,
                        'status' => 'active',
                        'priority' => 'medium',
                        'progress' => 5,
                        'color' => '#065175',
                        'start_date' => now()->toDateString(),
                    ]);

                    $quotation->update(['project_id' => $project->id]);

                    Notification::make()->title('Project started')->success()->send();
                }),
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SalarySlip;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MyProfilePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'My Profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected static ?string $slug = 'my-profile';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.my-profile';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $employee = $user?->employee;

        return [
            'user' => $user,
            'employee' => $employee,
            'slips' => $employee
                ? SalarySlip::query()->where('employee_id', $employee->id)->latest()->limit(12)->get()
                : collect(),
            'holidays' => Holiday::query()->whereDate('date', '>=', now()->startOfYear())->orderBy('date')->limit(20)->get(),
            'attendance' => $user
                ? Attendance::query()->where('user_id', $user->id)->latest('work_date')->limit(14)->get()
                : collect(),
            'joiningQr' => $employee?->joining_letter_qr
                ? app(\App\Support\QrCodeService::class)->pngDataUri(url('/admin/my-profile?jl='.$employee->joining_letter_qr))
                : null,
        ];
    }
}

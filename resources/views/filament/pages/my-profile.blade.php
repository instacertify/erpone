<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, #065175, #ec6820);">
            <h2 class="text-2xl font-semibold">{{ $user?->name }}</h2>
            <p class="mt-1 text-white/90">{{ $user?->email }} · {{ $user?->role?->label() }}</p>
            @if($employee)
                <p class="mt-2 text-sm text-white/80">Employee code: {{ $employee->employee_code }} · {{ $employee->job_title }}</p>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-[#065175]">Salary slips</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($slips as $slip)
                        <li class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                            <span>{{ $slip->period }} · ₹{{ number_format((float)$slip->net, 2) }}</span>
                            @if($slip->file_path)
                                <span class="text-[#ec6820]">Available</span>
                            @else
                                <span class="text-slate-400">On file</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-slate-500">No salary slips published yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-[#065175]">Joining letter QR</h3>
                @if($joiningQr)
                    <img src="{{ $joiningQr }}" alt="Joining letter QR" class="mt-3 h-40 w-40">
                    <p class="mt-2 text-xs text-slate-500">{{ $employee?->joining_letter_qr }}</p>
                @else
                    <p class="mt-3 text-sm text-slate-500">Ask HR/Admin to publish your joining letter.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-[#065175]">Holiday calendar</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($holidays as $holiday)
                        <li class="flex justify-between rounded-xl bg-[#fff7f1] px-3 py-2">
                            <span>{{ $holiday->name }}</span>
                            <span class="text-[#065175]">{{ $holiday->date->format('d M Y') }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">No holidays configured.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-[#065175]">Recent attendance</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($attendance as $row)
                        <li class="flex justify-between rounded-xl bg-slate-50 px-3 py-2">
                            <span>{{ $row->work_date->format('d M Y') }} · {{ ucfirst($row->status) }}</span>
                            <span class="text-slate-500">{{ $row->check_in }} – {{ $row->check_out }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">No attendance rows yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-filament-panels::page>

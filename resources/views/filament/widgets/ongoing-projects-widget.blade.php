<div class="rounded-2xl border border-[#065175]/15 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-[#065175]">Ongoing projects</h3>
            <p class="text-sm text-slate-500">Tile view of active consulting & certification work</p>
        </div>
        <a href="{{ url('/admin/projects') }}" class="text-sm font-semibold text-[#ec6820] hover:underline">View all</a>
    </div>

    @if ($projects->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
            No ongoing projects yet. Accept a quote to start one.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($projects as $project)
                @php
                    $accent = $project->color ?: '#065175';
                    $progress = (int) ($project->progress ?? 0);
                @endphp
                <a href="{{ url('/admin/projects') }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="absolute inset-x-0 top-0 h-1.5" style="background: linear-gradient(90deg, {{ $accent }}, #ec6820);"></div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-[#ec6820]">{{ $project->code }}</p>
                            <h4 class="mt-1 text-base font-semibold text-slate-900 group-hover:text-[#065175]">{{ $project->name }}</h4>
                            <p class="mt-1 text-sm text-slate-500">{{ $project->customer?->name ?? 'Unassigned customer' }}</p>
                        </div>
                        <span class="rounded-full bg-[#065175]/10 px-2.5 py-1 text-xs font-medium capitalize text-[#065175]">
                            {{ str_replace('_', ' ', $project->status) }}
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-xs text-slate-500">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full" style="width: {{ $progress }}%; background: linear-gradient(90deg, #065175, #ec6820);"></div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                        <span>Manager: {{ $project->manager?->name ?? '—' }}</span>
                        <span>{{ optional($project->due_date)->format('d M Y') ?? 'No due date' }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

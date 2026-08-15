<div class="h-full rounded-2xl border border-[#ec6820]/20 bg-gradient-to-b from-[#fff7f1] to-white p-5 shadow-sm">
    <h3 class="text-lg font-semibold text-[#065175]">Pending for you</h3>
    <p class="mb-4 text-sm text-slate-500">Quotes, tasks, and leads needing attention</p>

    @if (empty($items))
        <p class="rounded-xl bg-white/80 px-3 py-8 text-center text-sm text-slate-500">You’re clear — nothing pending.</p>
    @else
        <ul class="space-y-3">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['href'] }}"
                       class="block rounded-xl border border-white bg-white px-3 py-3 shadow-sm transition hover:border-[#065175]/30 hover:shadow">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $item['tone'] === 'orange' ? 'bg-[#ec6820]' : 'bg-[#065175]' }}"></span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $item['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['meta'] }}</p>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

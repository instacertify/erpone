<div class="ic-hero overflow-hidden rounded-2xl p-6 text-white shadow-lg"
     style="background: linear-gradient(135deg, #065175 0%, #0a6d9a 55%, #ec6820 140%);">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-white/80">Instacertify ERP</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight">
                {{ $greeting }}, {{ $name }}
            </h2>
            <p class="mt-2 max-w-2xl text-base text-white/90">
                Here’s your consulting workspace — leads, quotes, projects, and deliveries in one place.
            </p>
            <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-sm backdrop-blur">
                <span>{{ $role }}</span>
                <span class="opacity-60">•</span>
                <span>{{ $date }}</span>
                <span class="opacity-60">•</span>
                <span>{{ $time }} IST</span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <a href="{{ url('/admin/leads') }}" class="rounded-xl bg-white/15 px-4 py-3 text-center font-medium backdrop-blur transition hover:bg-white/25">CRM Leads</a>
            <a href="{{ url('/admin/customers') }}" class="rounded-xl bg-white/15 px-4 py-3 text-center font-medium backdrop-blur transition hover:bg-white/25">Customers</a>
            <a href="{{ url('/admin/quotations') }}" class="rounded-xl bg-[#ec6820] px-4 py-3 text-center font-semibold text-white shadow transition hover:brightness-110">Quotations</a>
            <a href="{{ url('/admin/projects') }}" class="rounded-xl bg-white px-4 py-3 text-center font-semibold text-[#065175] transition hover:bg-slate-100">Projects</a>
        </div>
    </div>
</div>

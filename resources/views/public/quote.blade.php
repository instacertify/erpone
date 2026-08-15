<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quote->number }} · Instacertify</title>
    <style>
        :root { --blue:#065175; --orange:#ec6820; --ink:#0f172a; --muted:#64748b; --bg:#f8fafc; }
        *{box-sizing:border-box} body{margin:0;font-family:Figtree,ui-sans-serif,system-ui,sans-serif;background:var(--bg);color:var(--ink)}
        .wrap{max-width:960px;margin:0 auto;padding:24px}
        .hero{background:linear-gradient(135deg,var(--blue),#0a6d9a 60%,var(--orange));color:#fff;border-radius:24px;padding:28px}
        .card{background:#fff;border-radius:20px;padding:20px;margin-top:16px;box-shadow:0 8px 24px rgba(6,81,117,.08)}
        table{width:100%;border-collapse:collapse} th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:14px}
        .btn{display:inline-block;border:0;border-radius:12px;padding:12px 18px;font-weight:700;cursor:pointer;text-decoration:none}
        .btn-accept{background:var(--orange);color:#fff} .btn-secondary{background:#e2e8f0;color:var(--blue)}
        textarea{width:100%;min-height:110px;border:1px solid #cbd5e1;border-radius:12px;padding:12px}
        .muted{color:var(--muted)} .grid{display:grid;gap:16px} @media(min-width:800px){.grid-2{grid-template-columns:2fr 1fr}}
        .flash{background:#ecfdf5;color:#065f46;padding:12px 16px;border-radius:12px;margin-bottom:12px}
        .badge{display:inline-block;background:rgba(255,255,255,.2);padding:4px 10px;border-radius:999px;font-size:12px}
    </style>
</head>
<body>
<div class="wrap">
    @if(session('status'))<div class="flash">{{ session('status') }}</div>@endif

    <div class="hero">
        <div class="badge">Instacertify Quotation</div>
        <h1 style="margin:12px 0 4px;font-size:32px">{{ $quote->number }}</h1>
        <p style="margin:0;opacity:.92">{{ $quote->customer?->name }} · {{ strtoupper($quote->currency) }} · {{ ucfirst(str_replace('_',' ',$quote->category)) }}</p>
        <p style="margin:8px 0 0;opacity:.85">Barcode / QR: {{ $quote->barcode }}</p>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h2 style="color:var(--blue);margin-top:0">Line items</h2>
            <table>
                <thead>
                <tr><th>Description</th><th>Type</th><th>Pay to</th><th>Amount</th></tr>
                </thead>
                <tbody>
                @forelse($quote->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($item->tests_required)<div class="muted">Tests: {{ $item->tests_required }}</div>@endif
                            @if($item->applicable_standard)<div class="muted">Standard: {{ $item->applicable_standard }}</div>@endif
                            @if($item->samples_count)<div class="muted">Samples: {{ $item->samples_count }}</div>@endif
                            @if($item->testing_timeline)<div class="muted">Timeline: {{ $item->testing_timeline }}</div>@endif
                            @if($item->lab)<div class="muted">Lab: {{ $item->lab->name }} ({{ $item->lab_accreditation }})</div>@endif
                        </td>
                        <td>{{ str_replace('_',' ', $item->item_type) }}</td>
                        <td>{{ $item->pay_to }}</td>
                        <td>{{ number_format((float)$item->line_total, 2) }} {{ $item->currency }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No line items listed.</td></tr>
                @endforelse
                </tbody>
            </table>
            <p style="margin-top:16px"><strong>Total:</strong> {{ number_format((float)$quote->total, 2) }} {{ $quote->currency }}
                @if($quote->currency !== 'INR')
                    <span class="muted">(≈ ₹{{ number_format((float)$quote->total_inr, 2) }})</span>
                @endif
            </p>
            <p class="muted">Our revenue (consulting + lab/testing charged to Instacertify): ₹{{ number_format((float)$quote->consulting_revenue + (float)$quote->lab_revenue, 2) }} · Passthrough (gov/lab direct): ₹{{ number_format((float)$quote->passthrough_total, 2) }}</p>
        </div>

        <div>
            <div class="card" style="text-align:center">
                <img src="{{ $qr }}" alt="Quote QR" style="width:180px;height:180px">
                <p class="muted">Scan to open this quotation</p>
            </div>
            <div class="card">
                <h3 style="color:var(--blue);margin-top:0">Certification timeline</h3>
                <p>{{ $quote->certification_timeline ?: 'Shared on request' }}</p>
                <h3 style="color:var(--blue)">Terms & conditions</h3>
                <p style="white-space:pre-wrap">{{ $quote->terms_and_conditions ?: 'Standard Instacertify terms apply.' }}</p>
                <h3 style="color:var(--blue)">Force majeure</h3>
                <p style="white-space:pre-wrap">{{ $quote->force_majeure ?: 'Delays due to circumstances beyond reasonable control may adjust timelines.' }}</p>
            </div>
        </div>
    </div>

    @if(!in_array($quote->status, ['accepted'], true))
        <div class="card">
            <form method="post" action="{{ url('/quote/'.$quote->share_token.'/accept') }}" style="display:inline">
                @csrf
                <button class="btn btn-accept" type="submit">Accept quotation</button>
            </form>
        </div>
        <div class="card">
            <h3 style="color:var(--orange);margin-top:0">Request changes</h3>
            <form method="post" action="{{ url('/quote/'.$quote->share_token.'/revise') }}">
                @csrf
                <textarea name="customer_remarks" placeholder="Describe the changes you need..." required>{{ old('customer_remarks', $quote->customer_remarks) }}</textarea>
                <div style="margin-top:12px">
                    <button class="btn btn-secondary" type="submit">Send revision remarks</button>
                </div>
            </form>
        </div>
    @else
        <div class="card"><strong>Status:</strong> Accepted on {{ optional($quote->accepted_at)->format('d M Y H:i') }}</div>
    @endif
</div>
</body>
</html>

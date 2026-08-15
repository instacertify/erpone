<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sample {{ $sample->sample_id }} · Instacertify</title>
    <style>
        body{margin:0;font-family:Figtree,ui-sans-serif,system-ui,sans-serif;background:#f8fafc;color:#0f172a}
        .wrap{max-width:720px;margin:0 auto;padding:24px}
        .hero{background:linear-gradient(135deg,#065175,#ec6820);color:#fff;border-radius:24px;padding:24px}
        .card{background:#fff;border-radius:20px;padding:20px;margin-top:16px;box-shadow:0 8px 24px rgba(6,81,117,.08)}
        .muted{color:#64748b}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1 style="margin:0 0 8px">Sample tracking</h1>
        <p style="margin:0;opacity:.92">{{ $sample->sample_id }} · {{ $sample->name }}</p>
    </div>
    <div class="card" style="text-align:center">
        <img src="{{ $qr }}" alt="Sample QR" style="width:200px;height:200px">
        <p class="muted">QR: {{ $sample->qr_code }}</p>
    </div>
    <div class="card">
        <p><strong>Customer:</strong> {{ $sample->customer?->name ?? '—' }}</p>
        <p><strong>Lab:</strong> {{ $sample->lab?->name ?? '—' }}</p>
        <p><strong>Status:</strong> {{ str_replace('_',' ', $sample->tracking_status ?: $sample->status) }}</p>
        <p><strong>Received:</strong> {{ optional($sample->received_at)->format('d M Y') ?? '—' }}</p>
        <p><strong>Dispatched:</strong> {{ optional($sample->dispatched_at)->format('d M Y H:i') ?? '—' }}</p>
        <p><strong>Testing started:</strong> {{ optional($sample->testing_started_at)->format('d M Y H:i') ?? '—' }}</p>
        <p><strong>Report:</strong> {{ optional($sample->report_uploaded_at)->format('d M Y H:i') ?? 'Not uploaded' }}</p>
    </div>
</div>
</body>
</html>

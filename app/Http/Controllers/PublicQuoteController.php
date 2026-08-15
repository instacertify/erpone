<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Sample;
use App\Models\User;
use App\Notifications\QuoteAcceptedNotification;
use App\Notifications\QuoteRevisionRequestedNotification;
use App\Support\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class PublicQuoteController extends Controller
{
    public function show(string $token)
    {
        $quote = Quotation::query()
            ->with(['customer', 'items.lab', 'service', 'creator'])
            ->where('share_token', $token)
            ->firstOrFail();

        $qr = app(QrCodeService::class)->pngDataUri(
            url('/quote/'.$quote->share_token)
        );

        return view('public.quote', [
            'quote' => $quote,
            'qr' => $qr,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $quote = Quotation::query()->where('share_token', $token)->firstOrFail();

        if ($quote->status === 'accepted') {
            return back()->with('status', 'This quotation was already accepted.');
        }

        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'rejected_at' => null,
            'customer_remarks' => null,
        ]);

        $this->notifyInternal($quote, QuoteAcceptedNotification::class);

        return back()->with('status', 'Thank you — quotation accepted. Instacertify has been notified.');
    }

    public function revise(Request $request, string $token)
    {
        $data = $request->validate([
            'customer_remarks' => ['required', 'string', 'max:5000'],
        ]);

        $quote = Quotation::query()->where('share_token', $token)->firstOrFail();

        $quote->update([
            'status' => 'revision_requested',
            'rejected_at' => now(),
            'customer_remarks' => $data['customer_remarks'],
        ]);

        $this->notifyInternal($quote, QuoteRevisionRequestedNotification::class);

        return back()->with('status', 'Your change request was sent to Instacertify.');
    }

    public function showSample(string $token)
    {
        $sample = Sample::query()
            ->with(['customer', 'lab', 'project'])
            ->where('share_token', $token)
            ->orWhere('qr_code', $token)
            ->firstOrFail();

        $qr = app(QrCodeService::class)->pngDataUri(
            url('/sample/'.$sample->share_token)
        );

        return view('public.sample', compact('sample', 'qr'));
    }

    /**
     * @param  class-string  $notification
     */
    protected function notifyInternal(Quotation $quote, string $notification): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($q) use ($quote) {
                $q->whereIn('role', ['super_admin', 'admin'])
                    ->orWhere('id', $quote->created_by)
                    ->orWhere('id', $quote->assigned_to);
            })
            ->get();

        Notification::send($recipients, new $notification($quote));
    }
}

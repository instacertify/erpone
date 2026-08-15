<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteRevisionRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public Quotation $quotation) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Revision requested for quotation {$this->quotation->number}")
            ->line("The customer requested a revision to quotation {$this->quotation->number}.")
            ->when(
                filled($this->quotation->customer_remarks),
                fn (MailMessage $message): MailMessage => $message
                    ->line("Customer remarks: {$this->quotation->customer_remarks}")
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'quotation_id' => $this->quotation->getKey(),
            'quotation_uuid' => $this->quotation->uuid,
            'quotation_number' => $this->quotation->number,
            'customer_remarks' => $this->quotation->customer_remarks,
            'message' => "A revision was requested for quotation {$this->quotation->number}.",
        ];
    }
}

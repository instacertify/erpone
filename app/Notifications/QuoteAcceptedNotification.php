<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAcceptedNotification extends Notification
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
            ->subject("Quotation {$this->quotation->number} accepted")
            ->line("Quotation {$this->quotation->number} has been accepted by the customer.");
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
            'message' => "Quotation {$this->quotation->number} has been accepted.",
        ];
    }
}

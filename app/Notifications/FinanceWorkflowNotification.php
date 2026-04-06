<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FinanceWorkflowNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly array $payload,
        private readonly bool $sendMail = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendMail && filled($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject((string) ($this->payload['subject'] ?? 'Notifikasi Keuangan'))
            ->greeting('Halo ' . ($notifiable->name ?? 'User') . ',')
            ->line((string) $this->payload['message'])
            ->action((string) ($this->payload['action_text'] ?? 'Lihat Detail'), (string) $this->payload['url'])
            ->line('Pesan ini dikirim dari sistem keuangan keluarga.');
    }
}

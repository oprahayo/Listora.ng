<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $eventType,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return config('listora.notification_channels', ['database']);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }
}

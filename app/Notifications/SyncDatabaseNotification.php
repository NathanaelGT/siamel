<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SyncDatabaseNotification extends Notification
{
    public function __construct(
        public array $data,
    )
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

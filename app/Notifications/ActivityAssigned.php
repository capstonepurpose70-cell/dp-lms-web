<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ActivityAssigned extends Notification
{
    public function __construct(
        public string $title,
        public string $subject,
        public string $instructor,
        public string $type,   // 'quiz' | 'module' | 'assignment'
        public string $url,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'      => $this->title,
            'subject'    => $this->subject,
            'instructor' => $this->instructor,
            'type'       => $this->type,
            'url'        => $this->url,
        ];
    }
}
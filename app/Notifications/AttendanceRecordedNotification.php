<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceRecordedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Attendance $attendance,
        public string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->type === 'check-in'
            ? $this->attendance->arrival_time->format('H:i:s')
            : $this->attendance->departure_time->format('H:i:s');

        $action = $this->type === 'check-in' ? 'Check-In' : 'Check-Out';

        return (new MailMessage)
            ->subject("Attendance {$action} Recorded")
            ->greeting("Hello {$notifiable->names}!")
            ->line("Your attendance has been recorded.")
            ->line("**{$action} Time:** {$time}")
            ->line("**Date:** {$this->attendance->date->format('Y-m-d')}")
            ->line('Thank you for using our attendance system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'type' => $this->type,
            'date' => $this->attendance->date,
        ];
    }
}

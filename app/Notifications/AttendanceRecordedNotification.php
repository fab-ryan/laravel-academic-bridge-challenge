<?php

namespace App\Notifications;

use App\Enums\AttendanceType;
use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when an attendance record is created or updated.
 */
class AttendanceRecordedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Attendance $attendance,
        public string $type
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $attendanceType = AttendanceType::tryFrom($this->type);
        $time = $this->type === AttendanceType::CHECK_IN->value
            ? $this->attendance->arrival_time->format('H:i:s')
            : $this->attendance->departure_time->format('H:i:s');

        $action = $attendanceType?->label() ?? ucfirst($this->type);

        return (new MailMessage)
            ->subject("Attendance {$action} Recorded")
            ->greeting("Hello {$notifiable->names}!")
            ->line("Your attendance has been recorded.")
            ->line("**{$action} Time:** {$time}")
            ->line("**Date:** {$this->attendance->date->format('Y-m-d')}")
            ->line('Thank you for using our attendance system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'type' => $this->type,
            'date' => $this->attendance->date,
        ];
    }
}

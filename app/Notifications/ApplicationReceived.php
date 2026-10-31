<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Only the application id is stored on the queued job. The model is
     * reloaded when the notification runs, so the job stays small and
     * always reflects the current data.
     */
    public function __construct(public int $applicationId)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $application = JobApplication::with('jobListing')->findOrFail($this->applicationId);

        return (new MailMessage)
            ->subject(__('New application for :title', ['title' => $application->jobListing->title]))
            ->line(__(':name (:email) applied for this job.', [
                'name' => $application->name,
                'email' => $application->email,
            ]))
            ->line(__('Message:'))
            ->line($application->message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->applicationId,
        ];
    }
}

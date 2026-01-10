<?php

namespace App\Notifications;

use App\Models\Team\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Invitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invite.accept', ['invitation' => $this->invitation->id]);
        $teamName = $this->invitation->team->name;
        $roleName = $this->invitation->role->name ?? $this->invitation->role->code;

        return (new MailMessage)
            ->subject(__('Invitation to join :team', ['team' => $teamName]))
            ->greeting(__('Hello!'))
            ->line(__('You have been invited to join the team ":team" as :role.', [
                'team' => $teamName,
                'role' => $roleName,
            ]))
            ->action(__('Accept Invitation'), $url)
            ->line(__('This invitation will allow you to collaborate with the team and access shared diagrams.'))
            ->line(__('If you did not expect this invitation, you can safely ignore this email.'));
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'team_invitation',
            'team_id' => $this->invitation->team_id,
            'team_name' => $this->invitation->team->name,
            'role_id' => $this->invitation->role_id,
            'role_name' => $this->invitation->role->name ?? $this->invitation->role->code,
            'invitation_id' => $this->invitation->id,
            'invited_at' => $this->invitation->created_at->toDateTimeString(),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_invitation',
            'team_id' => $this->invitation->team_id,
            'team_name' => $this->invitation->team->name,
            'role_name' => $this->invitation->role->name ?? $this->invitation->role->code,
            'invitation_id' => $this->invitation->id,
        ];
    }
}

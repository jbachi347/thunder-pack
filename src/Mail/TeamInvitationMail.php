<?php

namespace ThunderPack\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use ThunderPack\Models\TeamInvitation;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $acceptUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(TeamInvitation $invitation)
    {
        $this->invitation = $invitation;
        $this->acceptUrl = route('thunder-pack.invitations.accept', ['token' => $invitation->token]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tenantName = $this->invitation->tenant->name;
        $roleName = $this->getRoleName($this->invitation->role);
        
        return new Envelope(
            subject: "Invitación para unirte al equipo de {$tenantName} como {$roleName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'thunder-pack::emails.team-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getRoleName($role): string
    {
        return match($role) {
            'owner' => 'Propietario',
            'admin' => 'Administrador', 
            'staff' => 'Colaborador',
            default => 'Miembro'
        };
    }
}

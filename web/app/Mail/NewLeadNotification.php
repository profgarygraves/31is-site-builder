<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $site = $this->lead->site;
        $name = $this->lead->name() ?: 'Someone';
        return new Envelope(
            subject: "New lead on {$site->subdomain}: {$name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.leads.new',
            with: [
                'lead' => $this->lead,
                'site' => $this->lead->site,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

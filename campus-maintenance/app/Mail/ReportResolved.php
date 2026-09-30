<?php

namespace App\Mail;

use App\Models\MaintenanceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportResolved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MaintenanceReport $report)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Good news: {$this->report->reference} has been fixed",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report-resolved');
    }
}

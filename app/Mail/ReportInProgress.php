<?php

namespace App\Mail;

use App\Models\MaintenanceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportInProgress extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MaintenanceReport $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'We are working on your report');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report-in-progress');
    }
}
<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, Helvetica, sans-serif; color: #1b2440; line-height: 1.6; max-width: 560px; margin: 0 auto; padding: 16px;">
    <h2 style="color: #16296b; margin-bottom: 8px;">Good news, {{ $report->reporter_name }}!</h2>

    <p>
        Your report <strong>{{ $report->reference }}</strong> ({{ $report->title }}) at
        {{ $report->building }}, {{ $report->room }} has been marked as <strong style="color: #1f8a5b;">fixed</strong>.
    </p>

    <p>
        Fixed on {{ ($report->resolved_at ?? now())->format('M d, Y g:i A') }}@if ($report->assigned_to) by {{ $report->assigned_to }}@endif.
    </p>

    <p>
        If the problem is still there, please send a new report and mention {{ $report->reference }}.
    </p>

    <p>Thank you for helping keep our school safe and comfortable.<br>
        <strong>Campus Fix-It Desk</strong>
    </p>
</body>
</html>

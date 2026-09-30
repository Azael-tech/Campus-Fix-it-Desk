<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, Helvetica, sans-serif; color: #1b2440; line-height: 1.6; max-width: 560px;">
    <h2 style="color: #16296b; margin-bottom: 8px;">Hi {{ $report->reporter_name }},</h2>

    <p>
        Your report <strong>{{ $report->reference }}</strong> ({{ $report->title }}) at
        {{ $report->building }}, {{ $report->room }} is now
        <strong style="color: #e37b19;">in progress</strong>.
    </p>

    <p>Our maintenance team is working on it. We will email you again when it is fixed.</p>

    <p>Thank you for your patience.<br>
        <strong>Campus Fix-It Desk</strong>
    </p>
</body>
</html>
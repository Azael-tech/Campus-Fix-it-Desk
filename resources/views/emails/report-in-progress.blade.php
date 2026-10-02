<!DOCTYPE html>
<html lang="en">
<body style="font-family: Arial, Helvetica, sans-serif; color: #1b2440; line-height: 1.6; max-width: 560px; margin: 0 auto; padding: 16px;">
    <h2 style="color: #16296b; margin-bottom: 8px;">Hi {{ $report->reporter_name }}, we are on it!</h2>

    <p>
        Your report <strong>{{ $report->reference }}</strong> ({{ $report->title }}) at
        {{ $report->building }}, {{ $report->room }} is now <strong style="color: #2452c7;">in progress</strong>.
    </p>

    <p>
        @if ($report->assigned_to)
            {{ $report->assigned_to }} is working on it.
        @else
            Our maintenance team is working on it.
        @endif
        We will email you again as soon as it is fixed.
    </p>

    <p>
        If you have more details to share, please send a new report and mention {{ $report->reference }}.
    </p>

    <p>Thank you for helping keep our school safe and comfortable.<br>
        <strong>Campus Fix-It Desk</strong>
    </p>
</body>
</html>
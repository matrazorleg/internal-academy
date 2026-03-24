<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Workshop Reminder</title>
    </head>
    <body>
        <p>Hello,</p>

        <p>This is a reminder for tomorrow's workshop:</p>

        <ul>
            <li><strong>Title:</strong> {{ $workshop->title }}</li>
            <li><strong>When:</strong> {{ $workshop->starts_at->format('Y-m-d H:i') }} - {{ $workshop->ends_at->format('H:i') }}</li>
        </ul>

        <p>See you there.</p>
    </body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1b1b18; background-color: #fdfdfc; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px; border: 1px solid #e3e3e0;">
        <h1 style="font-size: 18px; margin-bottom: 16px;">{{ config('app.name') }}</h1>
        <p style="font-size: 14px; line-height: 1.6;">
            {{ $explanation }}
        </p>
        <p style="font-size: 12px; color: #706f6c; margin-top: 24px;">
            Reference: {{ $refundRequest->id }}
        </p>
    </div>
</body>
</html>

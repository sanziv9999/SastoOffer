<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? config('app.name', 'Sasto Offer') }}</title>
</head>
<body style="margin: 0; padding: 0; background: #ffffff;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #ffffff; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border: 1px solid #d4d4d8; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="height: 4px; background: #111827; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 26px 26px 26px;">
                            @include('emails.partials.header')
                            @yield('content')
                            @include('emails.partials.footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
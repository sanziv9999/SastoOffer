<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? config('app.name', 'Sasto Offer') }}</title>
</head>
<body style="margin: 0; padding: 0; background: #ecfdf5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #ecfdf5; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background: #ffffff; border: 1px solid #99f6e4; border-radius: 12px; overflow: hidden;">
                    <tr>
                        <td style="height: 4px; background: #134e4a; font-size: 0; line-height: 0;">&nbsp;</td>
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

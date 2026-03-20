@if(!empty($actionText) && !empty($actionUrl))
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 20px 0;">
        <tr>
            <td style="border-radius: 8px; background: #0f766e;">
                <a href="{{ $actionUrl }}" style="display: inline-block; padding: 10px 18px; font-family: Arial, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 600;">
                    {{ $actionText }}
                </a>
            </td>
        </tr>
    </table>
@endif

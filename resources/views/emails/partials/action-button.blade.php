@if(!empty($actionText) && !empty($actionUrl))
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 22px 0;">
        <tr>
            <td style="border-radius: 10px; background: #134e4a; box-shadow: 0 2px 8px rgba(19, 78, 74, 0.25);">
                <a href="{{ $actionUrl }}" style="display: inline-block; padding: 12px 22px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 600;">
                    {{ $actionText }}
                </a>
            </td>
        </tr>
    </table>
@endif

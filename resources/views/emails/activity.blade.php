@extends('emails.layouts.base')

@section('content')
    @if(!empty($statusLabel))
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #0f766e; margin: 0 0 8px 0;">
            {{ $statusLabel }}
        </p>
    @endif

    <h1 style="font-family: Georgia, 'Times New Roman', serif; font-size: 22px; color: #134e4a; margin: 0 0 16px 0; line-height: 1.3;">
        {{ $title }}
    </h1>

    @foreach($lines as $line)
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.65; color: #334155; margin: 0 0 12px 0;">
            {{ $line }}
        </p>
    @endforeach

    @if(!empty($orderNumber) || !empty($partnerName))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 18px 0 4px 0; background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 10px;">
            <tr>
                <td style="padding: 14px 16px;">
                    @if(!empty($orderNumber))
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #475569; margin: 0 0 6px 0;">
                            <strong style="color: #134e4a;">Order</strong>
                            <span style="font-family: Consolas, 'Courier New', monospace; font-weight: 600; color: #0f766e;">{{ $orderNumber }}</span>
                        </p>
                    @endif
                    @if(!empty($partnerName))
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #475569; margin: 0;">
                            <strong style="color: #134e4a;">{{ $partnerLabel ?? 'Vendor' }}</strong> {{ $partnerName }}
                        </p>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    @include('emails.partials.order-line-items', ['lineItems' => $lineItems ?? []])

    @if(!empty($orderTotalFormatted))
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 4px 0 0 0;">
            <tr>
                <td align="right" style="padding: 8px 4px 0 0;">
                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 700; color: #134e4a; margin: 0;">
                        Order total: {{ $orderTotalFormatted }}
                    </p>
                </td>
            </tr>
        </table>
    @endif

    @include('emails.partials.action-button', ['actionText' => $actionText, 'actionUrl' => $actionUrl])

    @if(!empty($metaLabel) && !empty($metaValue))
        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #475569; margin: 16px 0 0 0;">
            <strong style="color: #134e4a;">{{ $metaLabel }}:</strong> {{ $metaValue }}
        </p>
    @endif
@endsection

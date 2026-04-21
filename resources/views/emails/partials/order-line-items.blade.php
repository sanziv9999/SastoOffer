{{--
  Expects: $lineItems as array of rows with keys:
  title, image (optional absolute URL), offer_type (optional), quantity, unit_price, line_total,
  redemption (optional: redeemed|pending)
--}}
@if(!empty($lineItems))
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 20px 0 8px 0; border-collapse: separate;">
        <tr>
            <td style="font-family: Georgia, 'Times New Roman', serif; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #0f766e; padding: 0 0 8px 0; border-bottom: 2px solid #ccfbf1;">
                Your offer details
            </td>
        </tr>
    </table>
    @foreach($lineItems as $row)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 0 0 14px 0; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #f8fafc;">
            <tr>
                @if(!empty($row['image']))
                    <td width="96" valign="top" style="padding: 12px; width: 96px;">
                        <img src="{{ $row['image'] }}" alt="{{ $row['title'] }}" width="80" height="80" style="display: block; width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff;">
                    </td>
                @endif
                <td valign="top" style="padding: 12px 12px 12px {{ !empty($row['image']) ? '0' : '12px' }};">
                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 700; color: #134e4a; margin: 0 0 4px 0; line-height: 1.35;">
                        {{ $row['title'] }}
                    </p>
                    @if(!empty($row['offer_type']))
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #475569; margin: 0 0 6px 0;">
                            {{ $row['offer_type'] }}
                        </p>
                    @endif
                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #64748b; margin: 0 0 6px 0;">
                        Qty {{ (int) ($row['quantity'] ?? 1) }}
                        @if(isset($row['unit_price']))
                            <span style="color: #94a3b8;"> · </span>
                            <span style="color: #475569;">Unit Rs. {{ number_format((float) $row['unit_price'], 2) }}</span>
                        @endif
                    </p>
                    @if(!empty($row['redemption']))
                        <p style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: 600; margin: 0; color: {{ $row['redemption'] === 'redeemed' ? '#b45309' : '#475569' }};">
                            @if($row['redemption'] === 'redeemed')
                                Redeemed at store
                            @else
                                Awaiting redemption
                            @endif
                        </p>
                    @endif
                </td>
                <td valign="top" align="right" style="padding: 12px; white-space: nowrap;">
                    <p style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 700; color: #134e4a; margin: 0;">
                        Rs. {{ number_format((float) ($row['line_total'] ?? 0), 2) }}
                    </p>
                </td>
            </tr>
        </table>
    @endforeach
@endif

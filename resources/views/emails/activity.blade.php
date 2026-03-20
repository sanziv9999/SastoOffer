@extends('emails.layouts.base')

@section('content')
    <h1 style="font-family: Arial, sans-serif; font-size: 20px; color: #0f172a; margin: 0 0 14px 0;">
        {{ $title }}
    </h1>

    @foreach($lines as $line)
        <p style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #334155; margin: 0 0 12px 0;">
            {{ $line }}
        </p>
    @endforeach

    @include('emails.partials.action-button', ['actionText' => $actionText, 'actionUrl' => $actionUrl])

    @if(!empty($metaLabel) && !empty($metaValue))
        <p style="font-family: Arial, sans-serif; font-size: 13px; color: #0f172a; margin: 14px 0 0 0;">
            <strong>{{ $metaLabel }}:</strong> {{ $metaValue }}
        </p>
    @endif
@endsection

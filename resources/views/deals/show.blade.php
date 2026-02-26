@extends('layouts.app')

@section('title', $deal->title)

@section('content')
<h1>{{ $deal->title }}</h1>
<p><strong>Status:</strong> {{ $deal->status }}</p>
<p><strong>Vendor:</strong> {{ $deal->vendor?->business_name ?? '—' }}</p>
<p><strong>Sub Category:</strong> {{ $deal->subCategory?->name ?? '—' }}</p>
<p><strong>Slug:</strong> {{ $deal->slug }}</p>
@if ($deal->short_description)
    <p>{{ $deal->short_description }}</p>
@endif

@if ($deal->offerTypes->isNotEmpty())
<h2 style="margin-top:1.5rem;">Offers & prices</h2>
<table style="margin:0.5rem 0;">
    <thead>
        <tr>
            <th>Offer type</th>
            <th>Original price</th>
            <th>Final price</th>
            <th>Savings</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($deal->offerTypes as $ot)
        @php $p = $ot->pivot; @endphp
        <tr>
            <td>{{ $ot->display_name }}</td>
            <td>{{ $p->currency_code ?? 'NPR' }} {{ number_format($p->original_price ?? 0, 2) }}</td>
            <td>{{ $p->currency_code ?? 'NPR' }} {{ number_format($p->final_price ?? $p->original_price ?? 0, 2) }}</td>
            <td>{{ $p->currency_code ?? 'NPR' }} {{ number_format($p->savings_amount ?? 0, 2) }} ({{ number_format($p->savings_percent ?? 0, 1) }}%)</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@include('partials.images_section', [
    'imageable' => $deal,
    'imageableType' => 'deal',
    'allowedAttributes' => ['cover', 'gallery'],
    'title' => 'Deal images',
])

<p><a href="{{ route('deals.edit', $deal) }}">Edit</a>
<form action="{{ route('deals.destroy', $deal) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this deal?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('deals.index') }}">Back to list</a></p>
@endsection

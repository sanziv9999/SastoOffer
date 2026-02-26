@extends('layouts.app')

@section('title', $offerType->display_name)

@section('content')
<h1>{{ $offerType->display_name }}</h1>
<p><strong>Name:</strong> {{ $offerType->name }}</p>
<p><strong>Slug:</strong> {{ $offerType->slug ?? '—' }}</p>
<p><strong>Active:</strong> {{ $offerType->is_active ? 'Yes' : 'No' }}</p>
@if ($offerType->description)<p>{{ $offerType->description }}</p>@endif

@php $rule = $offerType->calculation_rule ?? []; @endphp
<h2 style="margin-top:1rem;">Calculation rule</h2>
@if (!empty($rule))
    @if (!empty($rule['formula_final_price']))
        <p><strong>Formula:</strong> <code>{{ $rule['formula_final_price'] }}</code></p>
    @endif
    @if (!empty($rule['type']))
        <p><strong>Fallback type:</strong> {{ $rule['type'] }}</p>
    @endif
    @if (!empty($rule['display']))
        <p><strong>Display:</strong> {{ $rule['display'] }}</p>
    @endif
@else
    <p>—</p>
@endif

<p><strong>Required params:</strong> {{ is_array($offerType->required_params) && count($offerType->required_params) ? implode(', ', $offerType->required_params) : '—' }}</p>
<p><strong>Default values:</strong> {{ is_array($offerType->default_values) && count($offerType->default_values) ? json_encode($offerType->default_values) : '—' }}</p>
<p><a href="{{ route('offer-types.edit', $offerType) }}">Edit</a>
<form action="{{ route('offer-types.destroy', $offerType) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('offer-types.index') }}">Back to list</a></p>
@endsection

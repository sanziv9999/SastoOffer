@extends('layouts.app')

@section('title', $businessType->name)

@section('content')
<h1>{{ $businessType->name }}</h1>
<p><strong>Slug:</strong> {{ $businessType->slug ?? '—' }}</p>
<p><strong>Display Order:</strong> {{ $businessType->display_order }}</p>
@if ($businessType->description)<p>{{ $businessType->description }}</p>@endif

@include('partials.images_section', [
    'imageable' => $businessType,
    'imageableType' => 'business_type',
    'allowedAttributes' => ['icon', 'banner'],
    'title' => 'Business type images',
])

<p><a href="{{ route('business-types.edit', $businessType) }}">Edit</a>
<form action="{{ route('business-types.destroy', $businessType) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('business-types.index') }}">Back to list</a></p>
@endsection

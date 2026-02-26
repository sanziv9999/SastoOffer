@extends('layouts.app')

@section('title', $businessSubCategory->name)

@section('content')
<h1>{{ $businessSubCategory->name }}</h1>
<p><strong>Business Type:</strong> {{ $businessSubCategory->businessType?->name ?? '—' }}</p>
<p><strong>Slug:</strong> {{ $businessSubCategory->slug ?? '—' }}</p>
<p><strong>Active:</strong> {{ $businessSubCategory->is_active ? 'Yes' : 'No' }}</p>
@if ($businessSubCategory->description)<p>{{ $businessSubCategory->description }}</p>@endif

@include('partials.images_section', [
    'imageable' => $businessSubCategory,
    'imageableType' => 'business_sub_category',
    'allowedAttributes' => ['icon', 'banner', 'image'],
    'title' => 'Sub-category images',
])

<p><a href="{{ route('business-sub-categories.edit', $businessSubCategory) }}">Edit</a>
<form action="{{ route('business-sub-categories.destroy', $businessSubCategory) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('business-sub-categories.index') }}">Back to list</a></p>
@endsection

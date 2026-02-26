@extends('layouts.app')

@section('title', 'Edit Business Type')

@section('content')
<h1>Edit Business Type</h1>
<form action="{{ route('business-types.update', $businessType) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $businessType->name) }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $businessType->slug) }}" maxlength="255">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description', $businessType->description) }}</textarea>
    </div>
    <div class="form-group">
        <label>Display Order</label>
        <input type="number" name="display_order" value="{{ old('display_order', $businessType->display_order) }}" min="0">
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $businessType->is_active) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit">Update</button>
</form>

@include('partials.images_section', [
    'imageable' => $businessType,
    'imageableType' => 'business_type',
    'allowedAttributes' => ['icon', 'banner'],
    'title' => 'Business type images',
])

<p><a href="{{ route('business-types.show', $businessType) }}">View</a> | <a href="{{ route('business-types.index') }}">Back to list</a></p>
@endsection

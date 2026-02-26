@extends('layouts.app')

@section('title', 'Edit Sub Category')

@section('content')
<h1>Edit Business Sub Category</h1>
<form action="{{ route('business-sub-categories.update', $businessSubCategory) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Business Type</label>
        <select name="business_type_id" required>
            @foreach ($businessTypes as $bt)
                <option value="{{ $bt->id }}" {{ old('business_type_id', $businessSubCategory->business_type_id) == $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $businessSubCategory->name) }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $businessSubCategory->slug) }}">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description', $businessSubCategory->description) }}</textarea>
    </div>
    <div class="form-group">
        <label>Display Order</label>
        <input type="number" name="display_order" value="{{ old('display_order', $businessSubCategory->display_order) }}" min="0">
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $businessSubCategory->is_active) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit">Update</button>
</form>

@include('partials.images_section', [
    'imageable' => $businessSubCategory,
    'imageableType' => 'business_sub_category',
    'allowedAttributes' => ['icon', 'banner', 'image'],
    'title' => 'Sub-category images',
])

<p><a href="{{ route('business-sub-categories.show', $businessSubCategory) }}">View</a> | <a href="{{ route('business-sub-categories.index') }}">Back to list</a></p>
@endsection

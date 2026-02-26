@extends('layouts.app')

@section('title', 'Create Sub Category')

@section('content')
<h1>Create Business Sub Category</h1>
<form action="{{ route('business-sub-categories.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Business Type</label>
        <select name="business_type_id" required>
            @foreach ($businessTypes as $bt)
                <option value="{{ $bt->id }}" {{ old('business_type_id') == $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label>Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug') }}">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description') }}</textarea>
    </div>
    <div class="form-group">
        <label>Display Order</label>
        <input type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0">
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit">Create</button>
</form>
<p><a href="{{ route('business-sub-categories.index') }}">Back to list</a></p>
@endsection

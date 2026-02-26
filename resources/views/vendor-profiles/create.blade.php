@extends('layouts.app')

@section('title', 'Create Vendor')

@section('content')
<h1>Create Vendor Profile</h1>
<form action="{{ route('vendor-profiles.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>User ID</label>
        <input type="number" name="user_id" value="{{ old('user_id') }}" required>
    </div>
    <div class="form-group">
        <label>Business Name</label>
        <input type="text" name="business_name" value="{{ old('business_name') }}" required maxlength="150">
    </div>
    <div class="form-group">
        <label>Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug') }}" maxlength="180">
    </div>
    <div class="form-group">
        <label>Business Type</label>
        <select name="business_type_id">
            <option value="">—</option>
            @foreach ($businessTypes as $bt)
                <option value="{{ $bt->id }}" {{ old('business_type_id') == $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>PAN Number</label>
        <input type="text" name="pan_number" value="{{ old('pan_number') }}" maxlength="50">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description') }}</textarea>
    </div>
    <div class="form-group">
        <label>Website URL</label>
        <input type="url" name="website_url" value="{{ old('website_url') }}">
    </div>
    <button type="submit">Create</button>
</form>
<p><a href="{{ route('vendor-profiles.index') }}">Back to list</a></p>
@endsection

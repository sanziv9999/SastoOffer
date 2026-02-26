@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<h1>Edit Vendor Profile</h1>
<form action="{{ route('vendor-profiles.update', $vendorProfile) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Business Name</label>
        <input type="text" name="business_name" value="{{ old('business_name', $vendorProfile->business_name) }}" required maxlength="150">
    </div>
    <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $vendorProfile->slug) }}" maxlength="180">
    </div>
    <div class="form-group">
        <label>Business Type</label>
        <select name="business_type_id">
            <option value="">—</option>
            @foreach ($businessTypes as $bt)
                <option value="{{ $bt->id }}" {{ old('business_type_id', $vendorProfile->business_type_id) == $bt->id ? 'selected' : '' }}>{{ $bt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>PAN Number</label>
        <input type="text" name="pan_number" value="{{ old('pan_number', $vendorProfile->pan_number) }}" maxlength="50">
    </div>
    <div class="form-group">
        <label>Verified Status</label>
        <select name="verified_status">
            @foreach (['pending','verified','rejected','suspended'] as $s)
                <option value="{{ $s }}" {{ old('verified_status', $vendorProfile->verified_status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Commission Rate</label>
        <input type="number" name="commission_rate" value="{{ old('commission_rate', $vendorProfile->commission_rate) }}" step="0.01" min="0" max="100">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description', $vendorProfile->description) }}</textarea>
    </div>
    <div class="form-group">
        <label>Website URL</label>
        <input type="url" name="website_url" value="{{ old('website_url', $vendorProfile->website_url) }}">
    </div>
    <div class="form-group">
        <label>Default location</label>
        <select name="default_location_id">
            <option value="">— None —</option>
            @foreach ($addresses as $addr)
                <option value="{{ $addr->id }}" {{ old('default_location_id', $vendorProfile->default_location_id) == $addr->id ? 'selected' : '' }}>
                    {{ $addr->address_line }}, {{ $addr->city }}
                </option>
            @endforeach
        </select>
        @if ($addresses->isEmpty())
            <small><a href="{{ route('addresses.create') }}">Add an address</a> first to set a default location.</small>
        @endif
    </div>
    <button type="submit">Update</button>
</form>

@include('partials.images_section', [
    'imageable' => $vendorProfile,
    'imageableType' => 'vendor_profile',
    'allowedAttributes' => ['logo', 'cover', 'gallery'],
    'title' => 'Vendor images',
])

<p><a href="{{ route('vendor-profiles.show', $vendorProfile) }}">View</a> | <a href="{{ route('vendor-profiles.index') }}">Back to list</a></p>
@endsection

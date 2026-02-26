@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<h1>Edit Customer Profile</h1>
<form action="{{ route('customer-profiles.update', $customerProfile) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="{{ old('full_name', $customerProfile->full_name) }}" maxlength="255">
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $customerProfile->phone) }}" maxlength="20">
    </div>
    <div class="form-group">
        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $customerProfile->date_of_birth?->format('Y-m-d')) }}">
    </div>
    <div class="form-group">
        <label>Gender</label>
        <select name="gender">
            <option value="">—</option>
            <option value="male" {{ old('gender', $customerProfile->gender) == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender', $customerProfile->gender) == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender', $customerProfile->gender) == 'other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>
    <div class="form-group">
        <label>Default address</label>
        <select name="default_address_id">
            <option value="">— None —</option>
            @foreach ($addresses as $addr)
                <option value="{{ $addr->id }}" {{ old('default_address_id', $customerProfile->default_address_id) == $addr->id ? 'selected' : '' }}>
                    {{ $addr->address_line }}, {{ $addr->city }}
                </option>
            @endforeach
        </select>
        @if ($addresses->isEmpty())
            <small><a href="{{ route('addresses.create') }}">Add an address</a> first to set a default.</small>
        @endif
    </div>
    <button type="submit">Update</button>
</form>

@include('partials.images_section', [
    'imageable' => $customerProfile,
    'imageableType' => 'customer_profile',
    'allowedAttributes' => ['profile_pic', 'gallery'],
    'title' => 'Profile images',
])

<p><a href="{{ route('customer-profiles.show', $customerProfile) }}">View</a> | <a href="{{ route('customer-profiles.index') }}">Back to list</a></p>
@endsection

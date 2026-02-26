@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
<h1>Create Customer Profile</h1>
<form action="{{ route('customer-profiles.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>User ID</label>
        <input type="number" name="user_id" value="{{ old('user_id') }}" required>
    </div>
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" value="{{ old('full_name') }}" maxlength="255">
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20">
    </div>
    <div class="form-group">
        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
    </div>
    <div class="form-group">
        <label>Gender</label>
        <select name="gender">
            <option value="">—</option>
            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>
    <div class="form-group">
        <label>Default Address ID</label>
        <input type="number" name="default_address_id" value="{{ old('default_address_id') }}" min="0">
    </div>
    <button type="submit">Create</button>
</form>
<p><a href="{{ route('customer-profiles.index') }}">Back to list</a></p>
@endsection

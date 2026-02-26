@extends('layouts.app')

@section('title', 'Edit Address')

@section('content')
<h1>Edit Address</h1>
<form action="{{ route('addresses.update', $address) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>User ID</label>
        <input type="number" name="user_id" value="{{ old('user_id', $address->user_id) }}" required>
    </div>
    <div class="form-group">
        <label>Address Line</label>
        <input type="text" name="address_line" value="{{ old('address_line', $address->address_line) }}" required>
    </div>
    <div class="form-group">
        <label>City</label>
        <input type="text" name="city" value="{{ old('city', $address->city) }}" required maxlength="100">
    </div>
    <div class="form-group">
        <label>State / Province</label>
        <input type="text" name="state_province" value="{{ old('state_province', $address->state_province) }}" maxlength="100">
    </div>
    <div class="form-group">
        <label>Postal Code</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" maxlength="20">
    </div>
    <div class="form-group">
        <label>Country Code</label>
        <input type="text" name="country_code" value="{{ old('country_code', $address->country_code) }}" required maxlength="2" size="2">
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_default" value="1" {{ old('is_default', $address->is_default) ? 'checked' : '' }}> Default</label>
    </div>
    <div class="form-group">
        <label>Label</label>
        <select name="label">
            <option value="">—</option>
            @foreach (['Home','Office','Work','Pickup Point','Friend/Family','Other','Warehouse'] as $l)
                <option value="{{ $l }}" {{ old('label', $address->label) == $l ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit">Update</button>
</form>
<p><a href="{{ route('addresses.show', $address) }}">View</a> | <a href="{{ route('addresses.index') }}">Back to list</a></p>
@endsection

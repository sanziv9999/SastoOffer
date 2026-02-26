@extends('layouts.app')

@section('title', 'Edit Offer Type')

@section('content')
<h1>Edit Offer Type</h1>
<form action="{{ route('offer-types.update', $offerType) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Name (internal)</label>
        <input type="text" name="name" value="{{ old('name', $offerType->name) }}" required maxlength="100">
    </div>
    <div class="form-group">
        <label>Display Name</label>
        <input type="text" name="display_name" value="{{ old('display_name', $offerType->display_name) }}" required maxlength="100">
    </div>
    <div class="form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $offerType->slug) }}" maxlength="120">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description', $offerType->description) }}</textarea>
    </div>
    <div class="form-group">
        <label>Formula (final price expression)</label>
        <input type="text" name="formula_final_price" value="{{ old('formula_final_price', $formData['formula_final_price'] ?? '') }}" maxlength="500" placeholder="e.g. original_price * (1 - discount_percent/100)">
    </div>
    <div class="form-group">
        <label>Rule type (fallback)</label>
        <input type="text" name="rule_type" value="{{ old('rule_type', $formData['rule_type'] ?? '') }}" maxlength="50" placeholder="e.g. percentage, fixed">
    </div>
    <div class="form-group">
        <label>Display template</label>
        <input type="text" name="display_template" value="{{ old('display_template', $formData['display_template'] ?? '') }}" maxlength="255" placeholder="e.g. {discount_percent}% OFF">
    </div>
    <div class="form-group">
        <label>Required params (comma-separated)</label>
        <input type="text" name="required_params_str" value="{{ old('required_params_str', $formData['required_params_str'] ?? '') }}" maxlength="500">
    </div>
    <div class="form-group">
        <label>Default values (JSON)</label>
        <textarea name="default_values_json" rows="3" placeholder='{"discount_percent": 10}'>{{ old('default_values_json', $formData['default_values_json'] ?? '') }}</textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $offerType->is_active) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit">Update</button>
</form>
<p><a href="{{ route('offer-types.show', $offerType) }}">View</a> | <a href="{{ route('offer-types.index') }}">Back to list</a></p>
@endsection

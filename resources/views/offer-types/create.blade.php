@extends('layouts.app')

@section('title', 'Create Offer Type')

@section('content')
<h1>Create Offer Type</h1>
<form action="{{ route('offer-types.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Name (internal)</label>
        <input type="text" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. percentage_discount">
    </div>
    <div class="form-group">
        <label>Display Name</label>
        <input type="text" name="display_name" value="{{ old('display_name') }}" required maxlength="100">
    </div>
    <div class="form-group">
        <label>Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug') }}" maxlength="120">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description">{{ old('description') }}</textarea>
    </div>
    <div class="form-group">
        <label>Formula (final price expression)</label>
        <input type="text" name="formula_final_price" value="{{ old('formula_final_price') }}" maxlength="500" placeholder="e.g. original_price * (1 - discount_percent/100)">
        <small class="form-text">Use variables: original_price and any param names (e.g. discount_percent, discount_amount).</small>
    </div>
    <div class="form-group">
        <label>Rule type (fallback)</label>
        <input type="text" name="rule_type" value="{{ old('rule_type') }}" maxlength="50" placeholder="e.g. percentage, fixed, bogo">
        <small class="form-text">Used when formula is missing or fails. Optional.</small>
    </div>
    <div class="form-group">
        <label>Display template</label>
        <input type="text" name="display_template" value="{{ old('display_template') }}" maxlength="255" placeholder="e.g. {discount_percent}% OFF">
        <small class="form-text">Use {param_name} for placeholders.</small>
    </div>
    <div class="form-group">
        <label>Required params (comma-separated)</label>
        <input type="text" name="required_params_str" value="{{ old('required_params_str') }}" maxlength="500" placeholder="e.g. discount_percent, min_order_value">
    </div>
    <div class="form-group">
        <label>Default values (JSON)</label>
        <textarea name="default_values_json" rows="3" placeholder='e.g. {"discount_percent": 10, "min_order_value": 1000}'>{{ old('default_values_json') }}</textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
    </div>
    <button type="submit">Create</button>
</form>
<p><a href="{{ route('offer-types.index') }}">Back to list</a></p>
@endsection

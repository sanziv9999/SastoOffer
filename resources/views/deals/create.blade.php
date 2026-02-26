@extends('layouts.app')

@section('title', 'Create Deal')

@section('content')
<h1>Create Deal</h1>
<form action="{{ route('deals.store') }}" method="POST">
    @csrf
    <div class="form-group">
        <label>Vendor</label>
        <select name="vendor_id" required>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->business_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Sub Category</label>
        <select name="business_sub_category_id" required>
            @foreach ($subCategories as $sc)
                <option value="{{ $sc->id }}" {{ old('business_sub_category_id') == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" value="{{ old('title') }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label>Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug') }}" maxlength="300">
    </div>
    <div class="form-group">
        <label>Short description</label>
        <textarea name="short_description">{{ old('short_description') }}</textarea>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="expired">Expired</option>
        </select>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}> Featured</label>
    </div>

    <h2 style="margin-top:1.5rem;">Offer types</h2>
    <p class="form-text">Select offer types by entering <strong>original price</strong> and rule values (e.g. discount %). Final price is calculated when you save.</p>
    @foreach ($offerTypes as $ot)
    @php
        $reqParams = is_array($ot->required_params) ? $ot->required_params : (is_string($ot->required_params) ? json_decode($ot->required_params, true) ?? [] : []);
        $defaults = is_array($ot->default_values) ? $ot->default_values : (is_string($ot->default_values) ? json_decode($ot->default_values, true) ?? [] : []);
    @endphp
    <fieldset style="margin:1rem 0; padding:1rem; border:1px solid #e5e7eb; border-radius:4px;">
        <legend><strong>{{ $ot->display_name }}</strong></legend>
        <div class="form-group">
            <label>Original price</label>
            <input type="number" name="offer_types[{{ $ot->id }}][original_price]" value="{{ old('offer_types.'.$ot->id.'.original_price') }}" min="0" step="0.01" placeholder="Enter to include this offer">
        </div>
        <div class="form-group">
            <label>Currency</label>
            <select name="offer_types[{{ $ot->id }}][currency_code]">
                <option value="NPR" {{ old('offer_types.'.$ot->id.'.currency_code', 'NPR') == 'NPR' ? 'selected' : '' }}>NPR</option>
                <option value="USD" {{ old('offer_types.'.$ot->id.'.currency_code') == 'USD' ? 'selected' : '' }}>USD</option>
            </select>
        </div>
        @foreach ($reqParams as $param)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $param)) }}</label>
            <input type="number" name="offer_types[{{ $ot->id }}][params][{{ $param }}]" value="{{ old('offer_types.'.$ot->id.'.params.'.$param, $defaults[$param] ?? '') }}" step="0.01" min="0">
        </div>
        @endforeach
    </fieldset>
    @endforeach

    <button type="submit">Create</button>
</form>
<p><a href="{{ route('deals.index') }}">Back to list</a></p>
@endsection

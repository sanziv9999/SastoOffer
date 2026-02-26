@extends('layouts.app')

@section('title', 'Edit Deal')

@section('content')
<h1>Edit Deal</h1>
<form action="{{ route('deals.update', $deal) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label>Vendor</label>
        <select name="vendor_id" required>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" {{ old('vendor_id', $deal->vendor_id) == $v->id ? 'selected' : '' }}>{{ $v->business_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Sub Category</label>
        <select name="business_sub_category_id" required>
            @foreach ($subCategories as $sc)
                <option value="{{ $sc->id }}" {{ old('business_sub_category_id', $deal->business_sub_category_id) == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" value="{{ old('title', $deal->title) }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label>Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug', $deal->slug) }}" maxlength="300">
    </div>
    <div class="form-group">
        <label>Short description</label>
        <textarea name="short_description">{{ old('short_description', $deal->short_description) }}</textarea>
    </div>
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            @foreach (['draft','active','inactive','expired'] as $s)
                <option value="{{ $s }}" {{ old('status', $deal->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $deal->is_featured) ? 'checked' : '' }}> Featured</label>
    </div>

    <h2 style="margin-top:1.5rem;">Offer types</h2>
    <p class="form-text">Select offer types by entering <strong>original price</strong>. Set discount/rule values (e.g. percentage) and save; final price is calculated automatically.</p>
    @foreach ($offerTypes as $ot)
    @php
        $pivot = $deal->offerTypes->firstWhere('id', $ot->id)?->pivot;
        $origPrice = old('offer_types.'.$ot->id.'.original_price', $pivot?->original_price);
        $currency = old('offer_types.'.$ot->id.'.currency_code', $pivot?->currency_code ?? 'NPR');
        $defaults = is_array($ot->default_values) ? $ot->default_values : (is_string($ot->default_values) ? json_decode($ot->default_values, true) ?? [] : []);
        $reqParams = is_array($ot->required_params) ? $ot->required_params : (is_string($ot->required_params) ? json_decode($ot->required_params, true) ?? [] : []);
        $pivotParams = $pivot && $pivot->params !== null ? (is_array($pivot->params) ? $pivot->params : (is_string($pivot->params) ? json_decode($pivot->params, true) ?? [] : [])) : [];
    @endphp
    <fieldset style="margin:1rem 0; padding:1rem; border:1px solid #e5e7eb; border-radius:4px;">
        <legend><strong>{{ $ot->display_name }}</strong></legend>
        @if ($pivot && ($pivot->final_price !== null || $pivot->original_price !== null))
        <p style="margin-bottom:0.75rem;"><strong>Calculated:</strong> {{ $pivot->currency_code ?? 'NPR' }} {{ number_format($pivot->final_price ?? $pivot->original_price ?? 0, 2) }} final
            @if (($pivot->savings_amount ?? 0) > 0)
                (savings {{ number_format($pivot->savings_amount ?? 0, 2) }} / {{ number_format($pivot->savings_percent ?? 0, 1) }}%)
            @endif
        </p>
        @endif
        <div class="form-group">
            <label>Original price</label>
            <input type="number" name="offer_types[{{ $ot->id }}][original_price]" value="{{ $origPrice }}" min="0" step="0.01" placeholder="Enter to include this offer">
        </div>
        <div class="form-group">
            <label>Currency</label>
            <select name="offer_types[{{ $ot->id }}][currency_code]">
                <option value="NPR" {{ $currency == 'NPR' ? 'selected' : '' }}>NPR</option>
                <option value="USD" {{ $currency == 'USD' ? 'selected' : '' }}>USD</option>
            </select>
        </div>
        @foreach ($reqParams as $param)
        <div class="form-group">
            <label>{{ ucfirst(str_replace('_', ' ', $param)) }}</label>
            @php $paramVal = old('offer_types.'.$ot->id.'.params.'.$param, $pivotParams[$param] ?? $defaults[$param] ?? ''); @endphp
            <input type="number" name="offer_types[{{ $ot->id }}][params][{{ $param }}]" value="{{ $paramVal }}" step="0.01" min="0">
        </div>
        @endforeach
    </fieldset>
    @endforeach

    <button type="submit">Update</button>
</form>

@include('partials.images_section', [
    'imageable' => $deal,
    'imageableType' => 'deal',
    'allowedAttributes' => ['cover', 'gallery'],
    'title' => 'Deal images',
])

<p><a href="{{ route('deals.show', $deal) }}">View</a> | <a href="{{ route('deals.index') }}">Back to list</a></p>
@endsection

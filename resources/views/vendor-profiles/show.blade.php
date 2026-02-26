@extends('layouts.app')

@section('title', $vendorProfile->business_name)

@section('content')
<h1>{{ $vendorProfile->business_name }}</h1>
<p><strong>Slug:</strong> {{ $vendorProfile->slug }}</p>
<p><strong>Business Type:</strong> {{ $vendorProfile->businessType?->name ?? '—' }}</p>
<p><strong>Verified Status:</strong> {{ $vendorProfile->verified_status }}</p>
<p><strong>Commission Rate:</strong> {{ $vendorProfile->commission_rate }}%</p>
@if ($vendorProfile->defaultLocation)
<p><strong>Default location:</strong> {{ $vendorProfile->defaultLocation->address_line }}, {{ $vendorProfile->defaultLocation->city }}</p>
@endif
@if ($vendorProfile->description)<p>{{ $vendorProfile->description }}</p>@endif

<h2>My addresses</h2>
<p><a href="{{ route('addresses.create') }}">Add address</a></p>
@if ($addresses->isNotEmpty())
<ul>
    @foreach ($addresses as $addr)
    <li>
        {{ $addr->address_line }}, {{ $addr->city }}{{ $addr->state_province ? ', ' . $addr->state_province : '' }} {{ $addr->postal_code }}
        ({{ $addr->label ?? '—' }}) @if ($addr->id == $vendorProfile->default_location_id) <em>— default location</em> @endif
        <a href="{{ route('addresses.edit', $addr) }}">Edit</a>
        <a href="{{ route('addresses.show', $addr) }}">View</a>
    </li>
    @endforeach
</ul>
@else
<p>No addresses yet. <a href="{{ route('addresses.create') }}">Add your first address</a>.</p>
@endif

@include('partials.images_section', [
    'imageable' => $vendorProfile,
    'imageableType' => 'vendor_profile',
    'allowedAttributes' => ['logo', 'cover', 'gallery'],
    'title' => 'Vendor images',
])

<p><a href="{{ route('vendor-profiles.edit', $vendorProfile) }}">Edit</a>
<form action="{{ route('vendor-profiles.destroy', $vendorProfile) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('vendor-profiles.index') }}">Back to list</a></p>
@endsection

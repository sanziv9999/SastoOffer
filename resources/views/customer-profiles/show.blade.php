@extends('layouts.app')

@section('title', $customerProfile->full_name ?? 'Customer')

@section('content')
<h1>{{ $customerProfile->full_name ?? 'Customer #' . $customerProfile->id }}</h1>
<p><strong>Phone:</strong> {{ $customerProfile->phone ?? '—' }}</p>
<p><strong>Date of Birth:</strong> {{ $customerProfile->date_of_birth?->format('Y-m-d') ?? '—' }}</p>
<p><strong>Gender:</strong> {{ $customerProfile->gender_display ?? '—' }}</p>
@if ($customerProfile->defaultAddress)
<p><strong>Default address:</strong> {{ $customerProfile->defaultAddress->address_line }}, {{ $customerProfile->defaultAddress->city }}</p>
@endif

<h2>My addresses</h2>
<p><a href="{{ route('addresses.create') }}">Add address</a></p>
@if ($addresses->isNotEmpty())
<ul>
    @foreach ($addresses as $addr)
    <li>
        {{ $addr->address_line }}, {{ $addr->city }}{{ $addr->state_province ? ', ' . $addr->state_province : '' }} {{ $addr->postal_code }}
        ({{ $addr->label ?? '—' }}) @if ($addr->id == $customerProfile->default_address_id) <em>— default</em> @endif
        <a href="{{ route('addresses.edit', $addr) }}">Edit</a>
        <a href="{{ route('addresses.show', $addr) }}">View</a>
    </li>
    @endforeach
</ul>
@else
<p>No addresses yet. <a href="{{ route('addresses.create') }}">Add your first address</a>.</p>
@endif

@include('partials.images_section', [
    'imageable' => $customerProfile,
    'imageableType' => 'customer_profile',
    'allowedAttributes' => ['profile_pic', 'gallery'],
    'title' => 'Profile images',
])

<p><a href="{{ route('customer-profiles.edit', $customerProfile) }}">Edit</a>
<form action="{{ route('customer-profiles.destroy', $customerProfile) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('customer-profiles.index') }}">Back to list</a></p>
@endsection

@extends('layouts.app')

@section('title', 'Address')

@section('content')
<h1>Address #{{ $address->id }}</h1>
<p>{{ $address->address_line }}</p>
<p>{{ $address->city }}{{ $address->state_province ? ', ' . $address->state_province : '' }} {{ $address->postal_code }}</p>
<p><strong>Country:</strong> {{ $address->country_code }}</p>
<p><strong>User ID:</strong> {{ $address->user_id }}</p>
<p><strong>Default:</strong> {{ $address->is_default ? 'Yes' : 'No' }}</p>
<p><a href="{{ route('addresses.edit', $address) }}">Edit</a>
<form action="{{ route('addresses.destroy', $address) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
</p>
<p><a href="{{ route('addresses.index') }}">Back to list</a></p>
@endsection

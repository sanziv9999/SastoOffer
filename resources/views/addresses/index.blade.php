@extends('layouts.app')

@section('title', 'Addresses')

@section('content')
<h1>Addresses</h1>
<p><a href="{{ route('addresses.create') }}">Create Address</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Address Line</th>
            <th>City</th>
            <th>Country</th>
            <th>User ID</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($addresses as $addr)
        <tr>
            <td>{{ $addr->id }}</td>
            <td>{{ Str::limit($addr->address_line, 40) }}</td>
            <td>{{ $addr->city }}</td>
            <td>{{ $addr->country_code }}</td>
            <td>{{ $addr->user_id }}</td>
            <td>
                <a href="{{ route('addresses.show', $addr) }}">View</a>
                <a href="{{ route('addresses.edit', $addr) }}">Edit</a>
                <form action="{{ route('addresses.destroy', $addr) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6">No addresses yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $addresses->links() }}
@endsection

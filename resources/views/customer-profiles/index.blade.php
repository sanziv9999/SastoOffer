@extends('layouts.app')

@section('title', 'Customer Profiles')

@section('content')
<h1>Customer Profiles</h1>
<p><a href="{{ route('customer-profiles.create') }}">Create Customer</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($customers as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->full_name ?? '—' }}</td>
            <td>{{ $c->phone ?? '—' }}</td>
            <td>
                <a href="{{ route('customer-profiles.show', $c) }}">View</a>
                <a href="{{ route('customer-profiles.edit', $c) }}">Edit</a>
                <form action="{{ route('customer-profiles.destroy', $c) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4">No customers yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $customers->links() }}
@endsection

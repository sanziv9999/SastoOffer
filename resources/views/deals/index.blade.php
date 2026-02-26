@extends('layouts.app')

@section('title', 'Deals')

@section('content')
<h1>Deals</h1>
<p><a href="{{ route('deals.create') }}">Create Deal</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Vendor</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($deals as $deal)
        <tr>
            <td>{{ $deal->id }}</td>
            <td>{{ $deal->title }}</td>
            <td>{{ $deal->vendor?->business_name ?? '—' }}</td>
            <td>{{ $deal->status }}</td>
            <td>
                <a href="{{ route('deals.show', $deal) }}">View</a>
                <a href="{{ route('deals.edit', $deal) }}">Edit</a>
                <form action="{{ route('deals.destroy', $deal) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this deal?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No deals yet.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $deals->links() }}
@endsection

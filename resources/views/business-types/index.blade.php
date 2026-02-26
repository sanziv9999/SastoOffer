@extends('layouts.app')

@section('title', 'Business Types')

@section('content')
<h1>Business Types</h1>
<p><a href="{{ route('business-types.create') }}">Create Business Type</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Order</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($businessTypes as $bt)
        <tr>
            <td>{{ $bt->id }}</td>
            <td>{{ $bt->name }}</td>
            <td>{{ $bt->slug ?? '—' }}</td>
            <td>{{ $bt->display_order }}</td>
            <td>
                <a href="{{ route('business-types.show', $bt) }}">View</a>
                <a href="{{ route('business-types.edit', $bt) }}">Edit</a>
                <form action="{{ route('business-types.destroy', $bt) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No business types yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $businessTypes->links() }}
@endsection

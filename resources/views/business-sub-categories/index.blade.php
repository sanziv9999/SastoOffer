@extends('layouts.app')

@section('title', 'Business Sub Categories')

@section('content')
<h1>Business Sub Categories</h1>
<p><a href="{{ route('business-sub-categories.create') }}">Create Sub Category</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Business Type</th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($subCategories as $sc)
        <tr>
            <td>{{ $sc->id }}</td>
            <td>{{ $sc->name }}</td>
            <td>{{ $sc->businessType?->name ?? '—' }}</td>
            <td>{{ $sc->is_active ? 'Yes' : 'No' }}</td>
            <td>
                <a href="{{ route('business-sub-categories.show', $sc) }}">View</a>
                <a href="{{ route('business-sub-categories.edit', $sc) }}">Edit</a>
                <form action="{{ route('business-sub-categories.destroy', $sc) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No sub categories yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $subCategories->links() }}
@endsection

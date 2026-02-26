@extends('layouts.app')

@section('title', 'Offer Types')

@section('content')
<h1>Offer Types</h1>
<p><a href="{{ route('offer-types.create') }}">Create Offer Type</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Display Name</th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($offerTypes as $ot)
        <tr>
            <td>{{ $ot->id }}</td>
            <td>{{ $ot->name }}</td>
            <td>{{ $ot->display_name }}</td>
            <td>{{ $ot->is_active ? 'Yes' : 'No' }}</td>
            <td>
                <a href="{{ route('offer-types.show', $ot) }}">View</a>
                <a href="{{ route('offer-types.edit', $ot) }}">Edit</a>
                <form action="{{ route('offer-types.destroy', $ot) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No offer types yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $offerTypes->links() }}
@endsection

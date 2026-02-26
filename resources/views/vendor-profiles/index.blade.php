@extends('layouts.app')

@section('title', 'Vendor Profiles')

@section('content')
<h1>Vendor Profiles</h1>
<p><a href="{{ route('vendor-profiles.create') }}">Create Vendor</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Business Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($vendors as $v)
        <tr>
            <td>{{ $v->id }}</td>
            <td>{{ $v->business_name }}</td>
            <td>{{ $v->businessType?->name ?? '—' }}</td>
            <td>{{ $v->verified_status }}</td>
            <td>
                <a href="{{ route('vendor-profiles.show', $v) }}">View</a>
                <a href="{{ route('vendor-profiles.edit', $v) }}">Edit</a>
                <form action="{{ route('vendor-profiles.destroy', $v) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No vendors yet.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $vendors->links() }}
@endsection

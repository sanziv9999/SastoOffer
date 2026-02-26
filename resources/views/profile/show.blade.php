@extends('layouts.app')

@section('title', 'My profile')

@section('content')
<h1>My profile</h1>
<p><strong>Name:</strong> {{ $user->name }}</p>
<p><strong>Email:</strong> {{ $user->email }}</p>
@if ($user->phone)<p><strong>Phone:</strong> {{ $user->phone }}</p>@endif

@include('partials.images_section', [
    'imageable' => $user,
    'imageableType' => 'user',
    'allowedAttributes' => ['avatar'],
    'title' => 'Profile photo (avatar)',
])

<p><a href="{{ route('deals.index') }}">Back</a></p>
@endsection

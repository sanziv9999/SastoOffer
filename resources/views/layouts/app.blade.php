<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SastoOffer')</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; padding: 1rem; }
        a { color: #2563eb; }
        .success { color: #16a34a; padding: 0.5rem 0; }
        nav { margin-bottom: 1.5rem; }
        nav a { margin-right: 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #f3f4f6; }
        input, select, textarea { padding: 0.25rem 0.5rem; margin: 0.25rem 0; }
        .form-group { margin-bottom: 0.75rem; }
        .form-text { display: block; font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <nav>
        @auth
            <a href="{{ route('profile.show') }}">Profile</a>
            <a href="{{ route('deals.index') }}">Deals</a>
            @role('admin')
                <a href="{{ route('vendor-profiles.index') }}">Vendors</a>
                <a href="{{ route('business-types.index') }}">Business Types</a>
                <a href="{{ route('business-sub-categories.index') }}">Sub Categories</a>
                <a href="{{ route('customer-profiles.index') }}">Customers</a>
                <a href="{{ route('offer-types.index') }}">Offer Types</a>
                <a href="{{ route('addresses.index') }}">Addresses</a>
            @endrole
            @role('vendor')
                @if(auth()->user()->vendorProfile)
                    <a href="{{ route('vendor-profiles.show', auth()->user()->vendorProfile) }}">My Vendor Profile</a>
                @else
                    <a href="{{ route('vendor-profiles.index') }}">Vendor Profile</a>
                @endif
            @endrole
            @role('customer')
                @if(auth()->user()->customerProfile)
                    <a href="{{ route('customer-profiles.show', auth()->user()->customerProfile) }}">My Profile</a>
                @else
                    <a href="{{ route('customer-profiles.index') }}">My Profile</a>
                @endif
            @endrole
            <span style="margin-left: auto;">{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" style="padding:0.25rem 0.5rem;cursor:pointer;">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        @endauth
    </nav>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    @yield('content')
</body>
</html>

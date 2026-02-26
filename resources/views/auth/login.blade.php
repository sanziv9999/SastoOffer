<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SastoOffer</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; padding: 2rem; max-width: 400px; margin-left: auto; margin-right: auto; }
        h1 { margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        input { width: 100%; padding: 0.5rem; box-sizing: border-box; }
        .error { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
        button { padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; cursor: pointer; margin-top: 0.5rem; }
        button:hover { background: #1d4ed8; }
        a { color: #2563eb; }
        .links { margin-top: 1rem; }
    </style>
</head>
<body>
    <h1>Login</h1>

    @if ($errors->any())
        <ul style="color: #dc2626; margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
        </div>
        <button type="submit">Login</button>
    </form>

    <p class="links">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
    <p><a href="{{ url('/') }}">Back to home</a></p>
</body>
</html>

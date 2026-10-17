<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Rep Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <h1 class="h3 mb-2">Sales Rep Tracker</h1>
                        <p class="text-muted mb-4">
                            Sign in to access your tracking page.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    class="form-control" autocomplete="username" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" name="password" type="password" class="form-control"
                                    autocomplete="current-password" required>
                            </div>

                            <div class="form-check mb-4">
                                <input id="remember" name="remember" type="checkbox" value="1"
                                    class="form-check-input">
                                <label for="remember" class="form-check-label">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Log In
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ url('/') }}" class="text-decoration-none">
                                Back to Home
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

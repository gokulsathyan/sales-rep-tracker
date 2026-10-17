<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Rep Registration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-md-6 col-lg-5">

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <h1 class="h3 mb-2">Create Account</h1>
                        <p class="text-muted mb-4">
                            Register as a sales representative.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name') }}"
                                    class="form-control"
                                    autocomplete="name"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    class="form-control"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">
                                    Confirm Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Register
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                Log in
                            </a>
                        </p>

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
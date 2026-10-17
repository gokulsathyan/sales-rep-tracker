
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sales Rep Tracker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                Sales Rep Tracker
            </a>

            <div class="d-flex align-items-center gap-3">
                @auth
                    <span class="text-muted d-none d-sm-inline">
                        Hi, {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            Log Out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                        Log In
                    </a>

                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-5">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5">
                <h1 class="h2 mb-3">Sales Rep Tracker</h1>

                <p class="text-muted mb-4">
                    Track field visits, review routes, and check location reports.
                </p>

                @auth
                    <p>
                        Logged in as {{ auth()->user()->email }}
                    </p>

                    <a href="{{ route('tracking') }}" class="btn btn-success">
                        Open Location Tracking
                    </a>
                @else
                    <p class="text-muted">
                        Log in to start recording your location during field visits.
                    </p>

                    <a href="{{ route('login') }}" class="btn btn-success">
                        Log In to Track
                    </a>
                @endauth
            </div>
        </div>

        <h2 class="h4 mb-3">Reports and Tools</h2>

        <div class="row g-3">
            <div class="col-md-6">
                <a href="{{ route('route-history') }}" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h3 class="h5">Route History and Reports</h3>
                            <p class="text-muted mb-0">
                                View route history, stop reports, and zone reports.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                @auth
                    <a href="{{ route('tracking') }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h5">Location Tracking</h3>
                                <p class="text-muted mb-0">
                                    Start or stop recording your current location.
                                </p>
                            </div>
                        </div>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="h5">Location Tracking</h3>
                                <p class="text-muted mb-0">
                                    Log in to access location tracking.
                                </p>
                            </div>
                        </div>
                    </a>
                @endauth
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sales Rep Location Tracking</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/js/tracking.js'])
</head>
<body class="bg-light">

    <header class="bg-white border-bottom">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-1">Sales Rep Tracker</h1>
                <p class="text-muted small mb-0">Location Tracking</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    Log Out
                </button>
            </form>
        </div>
    </header>

    <main class="container py-4" style="max-width: 900px;">

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-2">
                    Welcome, {{ auth()->user()->name }}
                </h2>

                <p class="text-muted mb-0">
                    Start tracking when you begin your field visit. Keep this page open
                    and allow location access to record your movement.
                </p>
            </div>
        </section>

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-4">Tracking Controls</h2>

                <div class="bg-light border rounded p-3 mb-4">
                    <p class="text-muted small mb-1">Tracking Status</p>
                    <p id="tracking-status" class="fw-semibold mb-0">
                        Not started
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button
                        id="start-tracking"
                        type="button"
                        class="btn btn-success px-4 py-2"
                    >
                        Start Tracking
                    </button>

                    <button
                        id="stop-tracking"
                        type="button"
                        disabled
                        class="btn btn-danger px-4 py-2"
                    >
                        Stop Tracking
                    </button>
                </div>

                <div
                    id="tracking-message"
                    role="status"
                    aria-live="polite"
                    class="mt-4 small text-muted"
                >
                    Tracking has not started yet.
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Latest Location</h2>

                <p class="mb-2">
                    <strong>Latitude:</strong>
                    <span id="latitude">—</span>
                </p>

                <p class="mb-2">
                    <strong>Longitude:</strong>
                    <span id="longitude">—</span>
                </p>

                <p class="mb-0">
                    <strong>Last recorded:</strong>
                    <span id="last-recorded">—</span>
                </p>
            </div>
        </section>

        <div class="mt-4">
            <a href="{{ url('/') }}" class="text-decoration-none">
                Back to Home
            </a>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sales Rep Route History</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">

    <style>
        #route-map {
            height: 450px;
            width: 100%;
        }

        .timeline-item {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body class="bg-light">

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="mb-0">Sales Rep Route History</h2>
            <div class="d-flex gap-2">
                <a href="/" class="btn btn-outline-dark">
                    Home
                </a>

                <a href="{{ route('stop-report') }}" class="btn btn-outline-success">
                    Stop Report
                </a>

                <a href="{{ route('zone-report') }}" class="btn btn-outline-primary">
                    Zone Report
                </a>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label for="rep-id" class="form-label">Representative</label>
                        <select id="rep-id" class="form-select">
                            <option value="">Loading representatives...</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="route-date" class="form-label">Date</label>
                        <input id="route-date" type="date" value="{{ now()->toDateString() }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button id="load-route" type="button" class="btn btn-primary w-100">
                            Load Route
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div id="status-message" class="mb-3"></div>

        <div class="card mb-4">
            <div class="card-header">Route Map</div>
            <div class="card-body p-2">
                <div id="route-map"></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Location Timeline</div>
            <div class="card-body">
                <div id="route-summary" class="mb-3 text-muted"></div>

                <div id="route-timeline">
                    Select a representative and date, then click Load Route.
                </div>
            </div>
        </div>

    </main>

    @vite(['resources/js/route-history.js'])

</body>

</html>

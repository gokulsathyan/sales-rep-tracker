<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Zone Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">

    <style>
        #route-map {
            height: 450px;
            width: 100%;
        }
    </style>
</head>

<body class="bg-light">
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="mb-0">Stop Report</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('route-history') }}" class="btn btn-outline-success">
                    Route History
                </a>

                <a href="{{ route('stop-report') }}" class="btn btn-outline-primary">
                    Stop Report
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label for="zone-date" class="form-label">Date</label>
                        <input id="zone-date" type="date" value="{{ now()->toDateString() }}" class="form-control">
                    </div>

                    <div class="mb-3" style="display: none">
                        <label for="zone-polygon" class="form-label">Zone Coordinates (JSON)</label>

                        <textarea id="zone-polygon" rows="5" class="form-control font-monospace"
                            placeholder="[[77.5900,12.9700],[77.6000,12.9700],[77.6000,12.9800],[77.5900,12.9800],[77.5900,12.9700]]" disabled></textarea>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">Route Map</div>
                        <div class="card-body p-2">
                            <div id="route-map"></div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button id="load-zone-report" type="button" class="btn btn-primary w-100">
                            Load Zone Report
                        </button>
                    </div>

                </div>
            </div>
        </div>



        <div id="zone-summary" class="my-3 text-danger"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Representative Name</th>
                        <th>Visits</th>
                        <th>Total Time Inside</th>
                    </tr>
                </thead>

                <tbody id="zone-report-body">
                    <tr>
                        <td colspan="3" class="text-muted">
                            Enter a zone and click Load Zone Report.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="zone-visit-details" class="mt-3"></div>
    </main>

    @vite(['resources/js/route-history.js'])
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Stop Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <style>
        .timeline-item {
            padding: 12px;
            border-bottom: 1px solid #ddd;
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
                        <button id="load-stops" type="button" class="btn btn-primary w-100">
                            Load Stop Report
                        </button>
                    </div>

                </div>

                <div id="status-message" class="mb-3"></div>

                <div id="stop-summary" class="mb-3 text-muted"></div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Duration</th>
                            </tr>
                        </thead>

                        <tbody id="stop-report-body">
                            <tr>
                                <td colspan="4" class="text-muted">
                                    Click Load Stop Report to view stationary locations.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    @vite(['resources/js/stop-report.js'])
</body>

</html>

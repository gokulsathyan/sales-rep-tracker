<?php

namespace App\Http\Controllers;

use App\Models\LocationLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LocationLogController extends Controller
{
    public function saveLocationLog(Request $request)
    {
        if (!$request->user()) {
            abort(401, 'Unauthorized.');
        }

        $validated = $request->validate([
            'locations' => 'required|array|min:1|max:500',
            'locations.*.event_id' => 'required|uuid|distinct',
            'locations.*.latitude' => 'required|numeric|between:-90,90',
            'locations.*.longitude' => 'required|numeric|between:-180,180',
            'locations.*.accuracy' => 'nullable|numeric|min:0|max:100000',
            'locations.*.recorded_at' => 'required|date',
        ]);

        $locations = $validated['locations'];
        $currentTime = Carbon::now()->toDateTimeString();
        $rows = [];

        foreach ($locations as $location) {
            $rows[] = [
                'user_id' => $request->user()->id,
                'event_id' => $location['event_id'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'accuracy' => $location['accuracy'] ?? null,
                'recorded_at' => Carbon::parse($location['recorded_at'])->setTimezone(config('app.timezone'))->toDateTimeString(),
                'received_at' => $currentTime,
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
            ];
        }

        $insertedCount = DB::transaction(function () use ($rows) {
            $eventIds = array_column($rows, 'event_id');
            $existingIds = DB::table('location_logs')
                ->whereIn('event_id', $eventIds)
                ->pluck('event_id')
                ->all();

            $existingLookup = array_fill_keys($existingIds, true);
            $newRows = [];

            foreach ($rows as $row) {
                if (!isset($existingLookup[$row['event_id']])) {
                    $newRows[] = $row;
                }
            }

            if (!empty($newRows)) {
                DB::table('location_logs')->insert($newRows);
            }

            return count($newRows);
        });

        return response()->json([
            'message' => 'Location logs processed successfully.',
            'status' => 200,
            'received' => count($locations),
            'inserted' => $insertedCount,
            'duplicates' => count($locations) - $insertedCount,
        ]);
    }
    public function getLocationHistory(Request $request, int $userId)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'];

        $start = Carbon::createFromFormat('!Y-m-d', $date);
        $end = $start->copy()->addDay();

        $points = LocationLog::query()
            ->where('user_id', $userId)
            ->where('recorded_at', '>=', $start)
            ->where('recorded_at', '<', $end)
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get([
                'id',
                'latitude',
                'longitude',
                'accuracy',
                'recorded_at',
            ]);


        return response()->json([
            'user_id' => $userId,
            'date' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
            'point_count' => $points->count(),
            'points' => $points,
        ]);
    }
    public function getStopReport(Request $request, int $userId)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'];

        $start = Carbon::createFromFormat('!Y-m-d', $date);
        $end = $start->copy()->addDay();

        $logs = LocationLog::query()
            ->where('user_id', $userId)
            ->where('recorded_at', '>=', $start)
            ->where('recorded_at', '<', $end)
            ->orderBy('recorded_at')
            ->get([
                'latitude',
                'longitude',
                'accuracy',
                'recorded_at',
            ]);

        $stops = [];
        $anchor = null;
        $last = null;

        foreach ($logs as $log) {
            $point = [
                'latitude' => (float) $log->latitude,
                'longitude' => (float) $log->longitude,
                'recorded_at' => Carbon::parse($log->recorded_at),
            ];

            if ($anchor === null) {
                $anchor = $point;
                $last = $point;
                continue;
            }

            $distance = $this->distanceInMeters(
                $anchor['latitude'],
                $anchor['longitude'],
                $point['latitude'],
                $point['longitude']
            );

            if ($distance <= 50) {
                $last = $point;
                continue;
            }

            $this->addStopIfQualified($stops, $anchor, $last);

            $anchor = $point;
            $last = $point;
        }

        if ($anchor !== null && $last !== null) {
            $this->addStopIfQualified($stops, $anchor, $last);
        }

        return response()->json([
            'user_id' => $userId,
            'date' => Carbon::parse($date)->format('d/m/Y'),
            'stop_count' => count($stops),
            'stops' => $stops,
        ]);
    }
    public function addStopIfQualified(array &$stops, array $anchor, array $last): void
    {
        $duration = $anchor['recorded_at']->diffInSeconds(
            $last['recorded_at']
        );

        if ($duration < 900) {
            return;
        }

        $stops[] = [
            'latitude' => $anchor['latitude'],
            'longitude' => $anchor['longitude'],
            'start_time' => $anchor['recorded_at']->toIso8601String(),
            'end_time' => $last['recorded_at']->toIso8601String(),
            'duration_minutes' => round($duration / 60, 2),
        ];
    }

    public function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2)
            * sin($deltaLon / 2) ** 2;

        return 2 * $earthRadius * asin(
            min(1, sqrt($a))
        );
    }
    public function getZoneReport(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'polygon' => ['required', 'array', 'min:4'],
            'polygon.*' => ['required', 'array', 'size:2'],
            'polygon.*.0' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'polygon.*.1' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
        ]);

        $polygon = $validated['polygon'];
        $first = $polygon[0];
        $last = $polygon[count($polygon) - 1];

        // Polygon coordinates must be [longitude, latitude].
        if (
            (float) $first[0] !== (float) $last[0] ||
            (float) $first[1] !== (float) $last[1]
        ) {
            throw ValidationException::withMessages([
                'polygon' => [
                    'The polygon must be closed: the first and last coordinates must match.',
                ],
            ]);
        }

        $start = Carbon::createFromFormat('!Y-m-d', $validated['date']);
        $end = $start->copy()->addDay();

        $logs = LocationLog::with('userDetail')
            ->where('recorded_at', '>=', $start)
            ->where('recorded_at', '<', $end)
            ->orderBy('user_id')
            ->orderBy('recorded_at')
            ->get([
                'user_id',
                'latitude',
                'longitude',
                'recorded_at',
            ]);

        $reports = [];

        foreach ($logs->groupBy('user_id') as $userId => $userLogs) {
            $visits = [];
            $visitStart = null;
            $lastInside = null;

            foreach ($userLogs as $log) {
                $inside = $this->pointInPolygon(
                    (float) $log->longitude,
                    (float) $log->latitude,
                    $polygon
                );

                $timestamp = Carbon::parse($log->recorded_at);

                if ($inside) {
                    if ($visitStart === null) {
                        $visitStart = $timestamp;
                    }

                    $lastInside = $timestamp;
                    continue;
                }

                if ($visitStart !== null) {
                    $this->addVisit(
                        $visits,
                        $visitStart,
                        $lastInside
                    );

                    $visitStart = null;
                    $lastInside = null;
                }
            }

            // Close a visit at the last observed point inside the zone.
            if ($visitStart !== null) {
                $this->addVisit(
                    $visits,
                    $visitStart,
                    $lastInside
                );
            }

            if (count($visits) > 0) {
                $totalSeconds = array_sum(
                    array_column($visits, 'duration_seconds')
                );

                $reports[] = [
                    'user_id' => (int) $userId,
                    'user_name' => $log['userDetail']['name'] . '-' . $log['userDetail']['email'],
                    'visit_count' => count($visits),
                    'total_time_inside_seconds' => $totalSeconds,
                    'total_time_inside_minutes' => round(
                        $totalSeconds / 60,
                        2
                    ),
                    'visits' => $visits,
                ];
            }
        }

        return response()->json([
            'date' => Carbon::parse($validated['date'])->toDateString(),
            'representative_count' => count($reports),
            'representatives' => $reports,
        ]);
    }

    private function addVisit(array &$visits, Carbon $start, Carbon $end): void
    {
        $seconds = max(0, $start->diffInSeconds($end));

        $visits[] = [
            'entry_time' => $start->format('d/m/Y H:i:s'),
            'exit_time' => $end->format('d/m/Y H:i:s'),
            'duration_seconds' => $seconds,
            'duration_minutes' => round($seconds / 60, 2),
        ];
    }

    private function pointInPolygon(float $longitude, float $latitude, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = (float) $polygon[$i][0];
            $yi = (float) $polygon[$i][1];
            $xj = (float) $polygon[$j][0];
            $yj = (float) $polygon[$j][1];

            $intersects = (
                ($yi > $latitude) !== ($yj > $latitude)
            ) && (
                $longitude < (
                    ($xj - $xi) * ($latitude - $yi)
                    / (($yj - $yi) ?: 0.000000000001)
                    + $xi
                )
            );

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}

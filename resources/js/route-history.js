
import $ from 'jquery';
import L from 'leaflet';
import { loadRepresentatives } from './helper.js';
import { summaryTextToggle } from './helper.js';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';

// Fix Leaflet marker icons when using Vite.
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

$(function () {
    const map = L.map('route-map').setView([12.9716, 77.5946], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);


    // Store the zone polygon drawn by the user.
    const drawnItems = L.featureGroup().addTo(map);
    map.addLayer(drawnItems);

    // Add polygon drawing and editing controls.
    const drawControl = new L.Control.Draw({
        position: 'topleft',
        draw: {
            polygon: {
                allowIntersection: false,
                showArea: false,
            },
            rectangle: false,
            polyline: false,
            circle: false,
            circlemarker: false,
            marker: false,
        },
        edit: {
            featureGroup: drawnItems,
            edit: {
                selectedPathOptions: {
                    maintainColor: false,
                },
            },
            remove: true,
        },
    });

    map.addControl(drawControl);

    // Save the polygon when drawing is completed.
    map.on(L.Draw.Event.CREATED, function (event) {
        console.log('Polygon created:', event.layer.getLatLngs());
        drawnItems.clearLayers();
        drawnItems.addLayer(event.layer);

        $('#zone-polygon').val(
            JSON.stringify(event.layer.getLatLngs()[0].map(function (point) {
                return [point.lng, point.lat];
            }).concat([
                [
                    event.layer.getLatLngs()[0][0].lng,
                    event.layer.getLatLngs()[0][0].lat,
                ],
            ]))
        );

        $('#zone-summary').text(
            'Zone drawn successfully. Select the date and click Load Zone Report.'
        );
        summaryTextToggle('success', 'zone-summary');
    });

    // Update coordinates if the polygon is edited.
    map.on(L.Draw.Event.EDITED, function (event) {
        event.layers.eachLayer(function (layer) {
            const points = layer.getLatLngs()[0];

            const polygon = points.map(function (point) {
                return [point.lng, point.lat];
            });

            polygon.push([points[0].lng, points[0].lat]);

            $('#zone-polygon').val(JSON.stringify(polygon));
        });
    });

    // Clear the coordinates if the polygon is deleted.
    map.on(L.Draw.Event.DELETED, function () {
        $('#zone-polygon').val('');
        $('#zone-summary').text('Zone cleared. Draw a new polygon to continue.');
        summaryTextToggle('success', 'zone-summary');
    });


    let routeLine = null;
    let routeMarkers = [];

    function clearRoute() {
        if (routeLine) {
            map.removeLayer(routeLine);
            routeLine = null;
        }

        routeMarkers.forEach(marker => map.removeLayer(marker));
        routeMarkers = [];
    }

    function showStatus(message, isError = false) {
        $('#status-message')
            .text(message)
            .toggleClass('text-red-600', isError)
            .toggleClass('text-gray-600', !isError);
    }

    function formatTimestamp(timestamp) {
        return new Date(timestamp).toLocaleString();
    }

    function loadRoute() {
        const userId = $('#rep-id').val();
        const date = $('#route-date').val();

        if (!userId || Number(userId) < 1 || !date) {
            showStatus('Select a valid representative and date.', true);
            return;
        }

        clearRoute();
        $('#route-timeline').empty();
        $('#route-summary').empty();
        showStatus('Loading route...');

        $.ajax({
            url: `/api/reps/${encodeURIComponent(userId)}/route`,
            method: 'GET',
            data: { date },
            dataType: 'json',
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${$('meta[name="api-token"]').attr('content')}`,
            },
        })
            .done(function (response) {
                const points = response.points || [];

                if (points.length === 0) {
                    showStatus('No location points found for this date.');
                    $('#route-summary').text('0 locations');
                    $('#route-timeline').text('No location history available.');
                    return;
                }

                const coordinates = points.map(point => [
                    point.latitude,
                    point.longitude,
                ]);

                routeLine = L.polyline(coordinates, {
                    color: '#2563eb',
                    weight: 4,
                }).addTo(map);

                points.forEach((point, index) => {
                    let marker;

                    if (index === 0) {
                        marker = L.marker(coordinates[index]);
                    } else if (index === points.length - 1) {
                        marker = L.marker(coordinates[index]);
                    } else {
                        marker = L.circleMarker(coordinates[index], {
                            radius: 5,
                            color: '#1d4ed8',
                            fillColor: '#60a5fa',
                            fillOpacity: 0.9,
                        });
                    }

                    const accuracy = point.accuracy === null
                        ? 'Unknown'
                        : `${point.accuracy} m`;

                    marker.bindPopup(`
                    <strong>${index === 0 ? 'Start' : index === points.length - 1 ? 'Latest point' : 'Location'}</strong><br>
                    Time: ${formatTimestamp(point.recorded_at)}<br>
                    Latitude: ${point.latitude}<br>
                    Longitude: ${point.longitude}<br>
                    GPS accuracy: ${accuracy}
                `);

                    marker.addTo(map);
                    routeMarkers.push(marker);

                    const $item = $('<div>').addClass('timeline-item');
                    $('<div>')
                        .addClass('font-medium')
                        .text(formatTimestamp(point.recorded_at))
                        .appendTo($item);

                    $('<div>')
                        .addClass('mt-1 text-sm text-gray-600')
                        .text(`Latitude: ${point.latitude}, Longitude: ${point.longitude}`)
                        .appendTo($item);

                    $('<div>')
                        .addClass('text-sm text-gray-600')
                        .text(`GPS accuracy: ${accuracy}`)
                        .appendTo($item);

                    $('#route-timeline').append($item);
                });

                map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

                $('#route-summary').text(
                    `${points.length} location points for ${response.date}`
                );

                showStatus('Route loaded successfully.');
            })
            .fail(function (xhr) {
                let message = 'Unable to load route.';

                if (xhr.status === 401 || xhr.status === 403) {
                    message = 'You are not authorized to view this route. Make sure you are logged in and viewing your own route.';
                } else if (xhr.status === 422) {
                    message = 'Invalid date. Please select a valid date.';
                }

                showStatus(message, true);
            });
    }

    $('#load-route').on('click', loadRoute);


    function loadZoneReport() {
        const date = $('#zone-date').val();
        const polygonText = $('#zone-polygon').val().trim();

        if (!date || !polygonText) {
            summaryTextToggle('fail', 'zone-summary');
            $('#zone-summary').text('Please select a date and enter zone coordinates.');
            return;
        }

        let polygon;

        try {
            polygon = JSON.parse(polygonText);
        } catch (error) {
            summaryTextToggle('fail', 'zone-summary');
            $('#zone-summary').text('Invalid JSON. Please check the zone coordinates.');
            return;
        }

        if (!Array.isArray(polygon) || polygon.length < 4) {
            summaryTextToggle('fail', 'zone-summary');
            $('#zone-summary').text('Enter a valid polygon with at least 4 coordinate pairs.');
            return;
        }

        $('#zone-report-body').empty();
        $('#zone-visit-details').empty();
        summaryTextToggle('success', 'zone-summary');
        $('#zone-summary').text('Loading zone report...');

        $.ajax({
            url: '/api/reports/zone',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: {
                Accept: 'application/json',
            },
            data: JSON.stringify({
                date: date,
                polygon: polygon,
            }),
        })
            .done(function (response) {
                const representatives = response.representatives || [];
                summaryTextToggle('success', 'zone-summary');
                $('#zone-summary').text(
                    `${response.representative_count} representative(s) entered the zone on ${response.date}.`
                );

                if (representatives.length === 0) {
                    $('#zone-report-body').append(
                        $('<tr>').append(
                            $('<td>')
                                .attr('colspan', 3)
                                .addClass('p-3 text-gray-500')
                                .text('No representatives were recorded inside this zone on the selected date.')
                        )
                    );

                    return;
                }

                representatives.forEach(function (rep) {
                    const $row = $('<tr>').addClass('border-b');

                    $('<td>')
                        .addClass('p-3')
                        .text(rep.user_name)
                        .appendTo($row);

                    $('<td>')
                        .addClass('p-3')
                        .text(rep.visit_count)
                        .appendTo($row);

                    $('<td>')
                        .addClass('p-3')
                        .text(`${rep.total_time_inside_minutes} minutes`)
                        .appendTo($row);

                    $('#zone-report-body').append($row);

                    const $details = $('<section>')
                        .addClass('mb-4 rounded border border-gray-200 p-3');

                    $('<h3>')
                        .addClass('mb-2 font-semibold')
                        .text(`Representative ${rep.user_name} — Visit Details`)
                        .appendTo($details);

                    const $visitList = $('<div>').addClass('space-y-2');

                    (rep.visits || []).forEach(function (visit, index) {
                        const $visit = $('<div>')
                            .addClass('rounded bg-gray-50 p-3 text-sm');

                        $('<div>')
                            .addClass('font-medium')
                            .text(`Visit ${index + 1}`)
                            .appendTo($visit);

                        $('<div>')
                            .text(`Entry: ${visit.entry_time}`)
                            .appendTo($visit);

                        $('<div>')
                            .text(`Exit: ${visit.exit_time}`)
                            .appendTo($visit);

                        $('<div>')
                            .text(`Duration: ${visit.duration_minutes} minutes`)
                            .appendTo($visit);

                        $visitList.append($visit);
                    });

                    $details.append($visitList);
                    $('#zone-visit-details').append($details);
                });
            })
            .fail(function (xhr) {
                let message = xhr.responseJSON?.message
                    || 'Unable to load the zone report.';

                const errors = xhr.responseJSON?.errors;

                if (errors) {
                    message = Object.values(errors).flat().join(' ');
                }
                summaryTextToggle('fail', 'zone-summary');
                $('#zone-summary').text(message);
            });
    }

    $('#load-zone-report').on('click', loadZoneReport);

    loadRepresentatives();
});

import $ from 'jquery';
import { loadRepresentatives } from './helper.js';
$(function () {
    loadRepresentatives();
    function loadStopReport() {
        const userId = $('#rep-id').val();
        const date = $('#route-date').val();

        if (!userId || Number(userId) < 1 || !date) {
            showStatus('Select a valid representative and date.', true);
            return;
        }

        $('#stop-report-body').empty();
        $('#stop-summary').text('Loading stop report...');

        $.ajax({
            url: `/api/reps/${encodeURIComponent(userId)}/stop-report`,
            method: 'GET',
            data: { date },
            dataType: 'json',
            headers: {
                Accept: 'application/json',
            },
        })
            .done(function (response) {
                const stops = response.stops || [];

                $('#stop-summary').text(
                    `${response.stop_count} qualifying stops for ${response.date}`
                );

                if (stops.length === 0) {
                    $('#stop-report-body').append(
                        $('<tr>').append(
                            $('<td>')
                                .attr('colspan', 4)
                                .addClass('p-3 text-gray-500')
                                .text('No qualifying stops found for this date.')
                        )
                    );

                    showStatus('Stop report loaded successfully.');
                    return;
                }

                stops.forEach(function (stop) {
                    const $row = $('<tr>').addClass('border-b');

                    $('<td>')
                        .addClass('p-3')
                        .text(`${stop.latitude}, ${stop.longitude}`)
                        .appendTo($row);

                    $('<td>')
                        .addClass('p-3')
                        .text(new Date(stop.start_time).toLocaleString())
                        .appendTo($row);

                    $('<td>')
                        .addClass('p-3')
                        .text(new Date(stop.end_time).toLocaleString())
                        .appendTo($row);

                    $('<td>')
                        .addClass('p-3')
                        .text(`${stop.duration_minutes} minutes`)
                        .appendTo($row);

                    $('#stop-report-body').append($row);
                });

                showStatus('Stop report loaded successfully.');
            })
            .fail(function (xhr) {
                $('#stop-summary').text('Unable to load stop report.');

                const message = xhr.responseJSON?.message
                    || 'An error occurred while loading the stop report.';

                showStatus(message, true);
            });
    }

    function showStatus(message, isError = false) {
        $('#status-message')
            .text(message)
            .toggleClass('text-red-600', isError)
            .toggleClass('text-gray-600', !isError);
    }

    $('#load-stops').on('click', loadStopReport);
});
import $ from 'jquery';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 3000,
    preventDuplicates: true
};
$(function () {
    const DB_NAME = 'sales_rep_tracking_db';
    const STORE_NAME = 'locations';
    const LEGACY_STORAGE_KEY = 'sales_rep_pending_locations';
    const BATCH_SIZE = 100;
    const RETRY_INTERVAL = 30000;

    const $startButton = $('#start-tracking');
    const $stopButton = $('#stop-tracking');
    const $status = $('#tracking-status');
    const $message = $('#tracking-message');

    let watchId = null;
    let isTracking = false;
    let isSending = false;
    let retryTimer = null;
    let dbPromise = null;

    function showStatus(message) {
        $status.text(message);
    }

    function showMessage(message) {
        $message.text(message);

        if (
            message.toLowerCase().includes('failed') ||
            message.toLowerCase().includes('could not') ||
            message.toLowerCase().includes('error') ||
            message.toLowerCase().includes('denied') ||
            message.toLowerCase().includes('expired') ||
            message.toLowerCase().includes('unavailable') ||
            message.toLowerCase().includes('not valid')
        ) {
            toastr.error(message);
        } else if (
            message.toLowerCase().includes('complete') ||
            message.toLowerCase().includes('saved')
        ) {
            toastr.success(message);
        } else {
            toastr.info(message);
        }
    }

    function openDatabase() {
        if (dbPromise) {
            return dbPromise;
        }

        dbPromise = new Promise(function (resolve, reject) {
            if (!window.indexedDB) {
                reject(new Error('IndexedDB is not supported.'));
                return;
            }

            const request = window.indexedDB.open(DB_NAME, 1);

            request.onupgradeneeded = function () {
                const db = request.result;

                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, {
                        keyPath: 'event_id'
                    });
                }
            };

            request.onerror = function () {
                reject(request.error || new Error('Could not open local storage.'));
            };

            request.onblocked = function () {
                showMessage('Close any older tracking tabs and try again.');
            };

            request.onsuccess = function () {
                const db = request.result;

                db.onversionchange = function () {
                    db.close();
                    dbPromise = null;
                };

                moveOldQueue(db)
                    .then(function () {
                        resolve(db);
                    })
                    .catch(function (error) {
                        db.close();
                        reject(error);
                    });
            };
        });

        dbPromise.catch(function () {
            dbPromise = null;
        });

        return dbPromise;
    }

    function moveOldQueue(db) {
        let oldQueue;

        try {
            const savedQueue = localStorage.getItem(LEGACY_STORAGE_KEY);

            if (savedQueue === null) {
                return Promise.resolve();
            }

            oldQueue = JSON.parse(savedQueue);

            if (!Array.isArray(oldQueue)) {
                throw new Error('The old location queue is invalid.');
            }
        } catch (error) {
            return Promise.reject(error);
        }

        return new Promise(function (resolve, reject) {
            let transaction;

            try {
                transaction = db.transaction(STORE_NAME, 'readwrite');
                const store = transaction.objectStore(STORE_NAME);

                oldQueue.forEach(function (location) {
                    if (location && typeof location.event_id === 'string') {
                        store.put(location);
                    }
                });
            } catch (error) {
                reject(error);
                return;
            }

            transaction.oncomplete = function () {
                try {
                    localStorage.removeItem(LEGACY_STORAGE_KEY);
                    resolve();
                } catch (error) {
                    reject(error);
                }
            };

            transaction.onerror = function () {
                reject(
                    transaction.error ||
                    new Error('Could not move saved locations.')
                );
            };

            transaction.onabort = function () {
                reject(
                    transaction.error ||
                    new Error('Moving saved locations was cancelled.')
                );
            };
        });
    }

    function getQueue() {
        return openDatabase().then(function (db) {
            return new Promise(function (resolve, reject) {
                const transaction = db.transaction(STORE_NAME, 'readonly');
                const request = transaction.objectStore(STORE_NAME).getAll();

                request.onsuccess = function () {
                    const locations = request.result || [];

                    locations.sort(function (a, b) {
                        return a.recorded_at.localeCompare(b.recorded_at);
                    });

                    resolve(locations);
                };

                request.onerror = function () {
                    reject(
                        request.error ||
                        new Error('Could not read saved locations.')
                    );
                };
            });
        });
    }

    function saveToQueue(location) {
        return openDatabase().then(function (db) {
            return new Promise(function (resolve, reject) {
                const transaction = db.transaction(STORE_NAME, 'readwrite');

                transaction.objectStore(STORE_NAME).add(location);

                transaction.oncomplete = resolve;

                transaction.onerror = function () {
                    reject(
                        transaction.error ||
                        new Error('Could not save the location.')
                    );
                };

                transaction.onabort = function () {
                    reject(
                        transaction.error ||
                        new Error('Saving the location was cancelled.')
                    );
                };
            });
        });
    }

    function removeFromQueue(eventIds) {
        if (!eventIds.length) {
            return Promise.resolve();
        }

        return openDatabase().then(function (db) {
            return new Promise(function (resolve, reject) {
                const transaction = db.transaction(STORE_NAME, 'readwrite');
                const store = transaction.objectStore(STORE_NAME);

                eventIds.forEach(function (eventId) {
                    store.delete(eventId);
                });

                transaction.oncomplete = resolve;

                transaction.onerror = function () {
                    reject(
                        transaction.error ||
                        new Error('Could not update the local queue.')
                    );
                };

                transaction.onabort = function () {
                    reject(
                        transaction.error ||
                        new Error('Updating the local queue was cancelled.')
                    );
                };
            });
        });
    }

    function createEventId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        if (
            !window.crypto ||
            typeof window.crypto.getRandomValues !== 'function'
        ) {
            throw new Error('Secure event IDs are not supported by this browser.');
        }

        const bytes = new Uint8Array(16);
        window.crypto.getRandomValues(bytes);

        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;

        const hex = Array.from(bytes, function (byte) {
            return byte.toString(16).padStart(2, '0');
        }).join('');

        return [
            hex.slice(0, 8),
            hex.slice(8, 12),
            hex.slice(12, 16),
            hex.slice(16, 20),
            hex.slice(20)
        ].join('-');
    }

    async function saveLocation(position) {
        const location = {
            event_id: createEventId(),
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            recorded_at: new Date(position.timestamp).toISOString()
        };

        try {
            await saveToQueue(location);

            const queue = await getQueue();

            showMessage(
                'Location saved. ' + queue.length +
                ' record(s) waiting to sync.'
            );

            sendPendingLocations();
        } catch (error) {
            showMessage(
                'Could not save the location in this browser. Check browser storage.'
            );
        }
    }

    async function sendPendingLocations() {
        if (isSending || !navigator.onLine) {
            return;
        }

        isSending = true;
        let sendNextBatch = false;

        try {
            const queue = await getQueue();

            if (!queue.length) {
                return;
            }

            const batch = queue.slice(0, BATCH_SIZE);

            const eventIds = batch.map(function (location) {
                return location.event_id;
            });

            let response;

            try {
                response = await $.ajax({
                    url: '/api/save-location-log',
                    method: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({
                        locations: batch
                    })
                });
            } catch (error) {
                if (error.status === 401 || error.status === 419) {
                    showMessage(
                        'Your session has expired. Log in again to sync saved locations.'
                    );
                } else {
                    showMessage(
                        'Sync failed. Saved locations will be retried.'
                    );
                }

                return;
            }

            if (
                !response ||
                response.status !== 200 ||
                typeof response.inserted !== 'number' ||
                typeof response.duplicates !== 'number'
            ) {
                showMessage(
                    'The server response was not valid. Locations are still saved locally.'
                );

                return;
            }

            await removeFromQueue(eventIds);

            const remaining = await getQueue();

            showMessage(
                'Sync complete: ' + response.inserted + ' inserted, ' +
                response.duplicates + ' duplicates. ' +
                remaining.length + ' record(s) waiting to sync.'
            );

            sendNextBatch = remaining.length > 0;
        } catch (error) {
            showMessage(
                'Could not read the saved locations. They have not been cleared from the queue.'
            );
        } finally {
            isSending = false;
        }

        if (sendNextBatch && navigator.onLine) {
            sendPendingLocations();
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            showStatus('Unsupported');
            showMessage('Your browser does not support location tracking.');
            return;
        }

        if (isTracking) {
            return;
        }

        isTracking = true;

        showStatus('Starting...');
        showMessage('Checking browser storage and requesting your location...');

        $startButton.prop('disabled', true);
        $stopButton.prop('disabled', false);

        openDatabase()
            .then(function () {
                if (!isTracking) {
                    return;
                }

                watchId = navigator.geolocation.watchPosition(
                    function (position) {
                        if (!isTracking) {
                            return;
                        }

                        $('#latitude').text(
                            position.coords.latitude.toFixed(6)
                        );

                        $('#longitude').text(
                            position.coords.longitude.toFixed(6)
                        );

                        $('#last-recorded').text(
                            new Date(position.timestamp).toLocaleString()
                        );

                        showStatus('Tracking');
                        saveLocation(position);
                    },
                    function (error) {
                        let message = 'Could not get your location.';

                        if (error.code === error.PERMISSION_DENIED) {
                            message =
                                'Location permission was denied. Allow location access and try again.';
                        } else if (
                            error.code === error.POSITION_UNAVAILABLE
                        ) {
                            message =
                                'Your current location is unavailable. Waiting for another update.';
                        } else if (error.code === error.TIMEOUT) {
                            message =
                                'The location request timed out. Waiting for another update.';
                        }

                        showMessage(message);

                        if (error.code === error.PERMISSION_DENIED) {
                            stopTracking();
                            showStatus('Permission denied');
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 15000
                    }
                );

                sendPendingLocations();
            })
            .catch(function () {
                isTracking = false;

                $startButton.prop('disabled', false);
                $stopButton.prop('disabled', true);

                showStatus('Storage error');

                showMessage(
                    'Browser storage is unavailable or saved locations could not be loaded. Tracking was not started.'
                );
            });
    }

    async function stopTracking() {
        isTracking = false;

        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }

        $startButton.prop('disabled', false);
        $stopButton.prop('disabled', true);

        showStatus('Stopped');

        try {
            const queue = await getQueue();

            showMessage(
                'Tracking stopped. ' + queue.length +
                ' record(s) waiting to sync.'
            );
        } catch (error) {
            showMessage(
                'Tracking stopped, but the saved queue could not be read.'
            );
        }

        sendPendingLocations();
    }

    function startRetryTimer() {
        if (retryTimer !== null) {
            return;
        }

        retryTimer = setInterval(sendPendingLocations, RETRY_INTERVAL);
    }

    function stopRetryTimer() {
        if (retryTimer !== null) {
            clearInterval(retryTimer);
            retryTimer = null;
        }
    }

    $startButton.on('click', startTracking);
    $stopButton.on('click', stopTracking);

    $(window).on('online', sendPendingLocations);

    $(document).on('visibilitychange', function () {
        if (!document.hidden) {
            sendPendingLocations();
        }
    });

    $(window).on('pagehide', stopRetryTimer);
    $(window).on('pageshow', startRetryTimer);

    startRetryTimer();

    getQueue()
        .then(function () {
            sendPendingLocations();
        })
        .catch(function () {
            showMessage(
                'Could not open the saved location queue. Check browser storage before tracking.'
            );
        });
});
Sales Rep Location Tracker

# Overview

This project is built using Laravel to track the locations of field sales representatives during their working hours.
The application collects GPS locations through the browser and saves them to the database. If the internet connection is unavailable, the locations are stored in the browser and synced with the server when the connection is back.
It also includes a route history page with a map and reports to check the areas covered by a representative.

To track locations, users must first register and log in. Once logged in, they can open the location tracking page and start tracking their locations.

Users can access the route history, stop report, and zone report pages without logging in. These pages are available to guest users.

## Tech Stack

1. Backend: PHP, Laravel
2. Database: MySQL
3. Frontend: Laravel Blade, JavaScript, jQuery
4. Maps: Leaflet and OpenStreetMap
5. Authentication: Laravel session authentication and Sanctum
6. Offline Storage: IndexedDB (for location tracking)
7. Location Tracking: Browser Geolocation API

## Steps

### Location Tracking
1. Users must register and log in to access the location tracking page.
2. Click Start Tracking to begin tracking. Locations are saved when movement is detected while the browser page is active.
3. If the browser page becomes inactive and is later active again, location tracking resumes automatically and new locations are saved when movement is detected.
4. Click Stop Tracking to stop tracking and save the final location.

#### How location tracking works:
1. Before sending a location to the server, it is stored in IndexedDB in the browser.
2. If the internet is unavailable or an upload fails, the pending locations stay in the local queue until they can be synced.
3. The application retries syncing when the internet connection comes back, when the page becomes active again, and periodically while the page is running.
4. Locations are sent to the server in batches of up to 100 records.
5. After the server confirms that the records have been handled successfully, they are removed from the local queue.
6. Each location has a unique event ID to prevent the duplicates when a request is retried.

## Route History and Reports

### Route history
1. Go to home page >> click Route history and reports.
2. To check the movement of representative - select representative and date in route history page and click load route.
3. Corresponding locations will be marked in the map.
4. Complete timeline of that representative for that day will be listed below the map.

### Stop Report
1. Go to stop report page.
2. Select represntative and date and click load stop report.
3. Details of representative who remained within a 50 metres radius for more than 15 minutes will be listed.

### Zone Report
1. Go to Zone report page.
2. Choose the date.
3. Click on the polygon shape in the map and draw the zone. Click on each point to start drawing and double click on a point to stop drawing.
4. Click Load zone report button to list all the representatives who entered that zone for the selected date.


# To Setup:
1. git clone <repository-url>
2. cd <project-directory>
3. Run
composer install
4. Configure .env file
5. Generate the application key:
php artisan key:generate
6. Create database and configure it in .env file
7. Run migrations
php artisan migrate
8. Run 
npm install
npm run build



**Note:** Migration files are included in the project. After creating the database and configuring the database credentials in your `.env` file, run `php artisan migrate` to create the required tables.
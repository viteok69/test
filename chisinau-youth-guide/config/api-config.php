<?php
// API Configuration for external data sources
define('GOOGLE_PLACES_API_KEY', 'YOUR_GOOGLE_PLACES_API_KEY'); // Get from Google Cloud Console
define('FOURSQUARE_API_KEY', 'YOUR_FOURSQUARE_API_KEY'); // Get from Foursquare Developer
define('OPENWEATHER_API_KEY', 'YOUR_OPENWEATHER_API_KEY'); // Optional for weather data

// API Endpoints
define('GOOGLE_PLACES_API_URL', 'https://maps.googleapis.com/maps/api/place');
define('FOURSQUARE_API_URL', 'https://api.foursquare.com/v3/places');

// Cache settings
define('CACHE_DURATION', 3600); // 1 hour cache for API data
define('CACHE_DIR', __DIR__ . '/../cache/');

// Create cache directory if it doesn't exist
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}
?>

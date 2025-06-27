<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/api-config.php';

class PlacesService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Fetch places from Google Places API for Chișinău
     */
    public function fetchPlacesFromGoogle($category = '', $radius = 5000) {
        $chisinau_coords = '47.0105,28.8638'; // Chișinău coordinates
        
        // Map our categories to Google Places types
        $type_mapping = [
            'Entertainment' => 'night_club|movie_theater|amusement_park|bowling_alley',
            'Education' => 'university|school|library',
            'Career' => 'establishment', // Will filter by keywords
            'Public Services' => 'hospital|local_government_office|transit_station'
        ];
        
        $type = isset($type_mapping[$category]) ? $type_mapping[$category] : '';
        
        $cache_key = 'google_places_' . md5($category . $radius);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = GOOGLE_PLACES_API_URL . '/nearbysearch/json?' . http_build_query([
            'location' => $chisinau_coords,
            'radius' => $radius,
            'type' => $type,
            'key' => GOOGLE_PLACES_API_KEY,
            'language' => 'ro'
        ]);
        
        $response = $this->makeApiRequest($url);
        
        if ($response && isset($response['results'])) {
            $places = $this->processGooglePlaces($response['results'], $category);
            $this->setCache($cache_key, $places);
            return $places;
        }
        
        return [];
    }
    
    /**
     * Fetch places from Foursquare API
     */
    public function fetchPlacesFromFoursquare($category = '', $limit = 50) {
        $chisinau_coords = '47.0105,28.8638';
        
        // Foursquare category IDs for Chișinău
        $category_mapping = [
            'Entertainment' => '4d4b7105d754a06376d81259', // Arts & Entertainment
            'Education' => '4d4b7105d754a06372d81259', // College & University
            'Career' => '4d4b7105d754a06375d81259', // Professional & Other Places
            'Public Services' => '4d4b7105d754a06377d81259' // Government Building
        ];
        
        $category_id = isset($category_mapping[$category]) ? $category_mapping[$category] : '';
        
        $cache_key = 'foursquare_places_' . md5($category . $limit);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = FOURSQUARE_API_URL . '/search?' . http_build_query([
            'll' => $chisinau_coords,
            'radius' => 5000,
            'categories' => $category_id,
            'limit' => $limit
        ]);
        
        $headers = [
            'Authorization: ' . FOURSQUARE_API_KEY,
            'Accept: application/json'
        ];
        
        $response = $this->makeApiRequest($url, $headers);
        
        if ($response && isset($response['results'])) {
            $places = $this->processFoursquarePlaces($response['results'], $category);
            $this->setCache($cache_key, $places);
            return $places;
        }
        
        return [];
    }
    
    /**
     * Get trending/recommended places
     */
    public function getRecommendedPlaces($limit = 6) {
        // Combine data from multiple sources
        $google_places = $this->fetchPlacesFromGoogle('Entertainment', 3000);
        $foursquare_places = $this->fetchPlacesFromFoursquare('Entertainment', 20);
        
        // Merge and sort by rating
        $all_places = array_merge($google_places, $foursquare_places);
        
        // Remove duplicates based on name similarity
        $unique_places = $this->removeDuplicatePlaces($all_places);
        
        // Sort by rating and popularity
        usort($unique_places, function($a, $b) {
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        });
        
        return array_slice($unique_places, 0, $limit);
    }
    
    /**
     * Search places by query
     */
    public function searchPlaces($query, $category = '', $limit = 20) {
        $chisinau_coords = '47.0105,28.8638';
        
        // Search in Google Places
        $google_results = $this->searchGooglePlaces($query, $chisinau_coords);
        
        // Search in Foursquare
        $foursquare_results = $this->searchFoursquarePlaces($query, $chisinau_coords);
        
        // Merge results
        $all_results = array_merge($google_results, $foursquare_results);
        
        // Filter by category if specified
        if ($category) {
            $all_results = array_filter($all_results, function($place) use ($category) {
                return $place['category'] === $category;
            });
        }
        
        // Remove duplicates and limit results
        $unique_results = $this->removeDuplicatePlaces($all_results);
        
        return array_slice($unique_results, 0, $limit);
    }
    
    /**
     * Search Google Places by text query
     */
    private function searchGooglePlaces($query, $location) {
        $cache_key = 'google_search_' . md5($query . $location);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = GOOGLE_PLACES_API_URL . '/textsearch/json?' . http_build_query([
            'query' => $query . ' Chișinău Moldova',
            'location' => $location,
            'radius' => 10000,
            'key' => GOOGLE_PLACES_API_KEY,
            'language' => 'ro'
        ]);
        
        $response = $this->makeApiRequest($url);
        
        if ($response && isset($response['results'])) {
            $places = $this->processGooglePlaces($response['results']);
            $this->setCache($cache_key, $places, 1800); // 30 min cache for searches
            return $places;
        }
        
        return [];
    }
    
    /**
     * Search Foursquare places by text query
     */
    private function searchFoursquarePlaces($query, $location) {
        $cache_key = 'foursquare_search_' . md5($query . $location);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        $url = FOURSQUARE_API_URL . '/search?' . http_build_query([
            'query' => $query,
            'll' => $location,
            'radius' => 10000,
            'limit' => 20
        ]);
        
        $headers = [
            'Authorization: ' . FOURSQUARE_API_KEY,
            'Accept: application/json'
        ];
        
        $response = $this->makeApiRequest($url, $headers);
        
        if ($response && isset($response['results'])) {
            $places = $this->processFoursquarePlaces($response['results']);
            $this->setCache($cache_key, $places, 1800);
            return $places;
        }
        
        return [];
    }
    
    /**
     * Process Google Places API response
     */
    private function processGooglePlaces($places, $default_category = '') {
        $processed = [];
        
        foreach ($places as $place) {
            // Determine category based on types
            $category = $this->determineCategory($place['types'] ?? [], $default_category);
            
            $processed[] = [
                'id' => 'google_' . $place['place_id'],
                'title' => $place['name'],
                'description' => $this->generateDescription($place),
                'category' => $category,
                'address' => $place['vicinity'] ?? $place['formatted_address'] ?? '',
                'latitude' => $place['geometry']['location']['lat'],
                'longitude' => $place['geometry']['location']['lng'],
                'rating' => $place['rating'] ?? 0,
                'image' => $this->getPlacePhoto($place),
                'phone' => '',
                'website' => '',
                'source' => 'google',
                'external_id' => $place['place_id']
            ];
        }
        
        return $processed;
    }
    
    /**
     * Process Foursquare Places API response
     */
    private function processFoursquarePlaces($places, $default_category = '') {
        $processed = [];
        
        foreach ($places as $place) {
            $category = $default_category ?: $this->mapFoursquareCategory($place['categories'][0]['name'] ?? '');
            
            $processed[] = [
                'id' => 'foursquare_' . $place['fsq_id'],
                'title' => $place['name'],
                'description' => $this->generateFoursquareDescription($place),
                'category' => $category,
                'address' => $place['location']['formatted_address'] ?? '',
                'latitude' => $place['geocodes']['main']['latitude'] ?? 0,
                'longitude' => $place['geocodes']['main']['longitude'] ?? 0,
                'rating' => ($place['rating'] ?? 0) / 2, // Convert from 10-scale to 5-scale
                'image' => '',
                'phone' => '',
                'website' => $place['website'] ?? '',
                'source' => 'foursquare',
                'external_id' => $place['fsq_id']
            ];
        }
        
        return $processed;
    }
    
    /**
     * Determine category from Google Places types
     */
    private function determineCategory($types, $default = 'Entertainment') {
        $category_mapping = [
            'night_club' => 'Entertainment',
            'movie_theater' => 'Entertainment',
            'amusement_park' => 'Entertainment',
            'bowling_alley' => 'Entertainment',
            'university' => 'Education',
            'school' => 'Education',
            'library' => 'Education',
            'hospital' => 'Public Services',
            'local_government_office' => 'Public Services',
            'transit_station' => 'Public Services'
        ];
        
        foreach ($types as $type) {
            if (isset($category_mapping[$type])) {
                return $category_mapping[$type];
            }
        }
        
        return $default;
    }
    
    /**
     * Map Foursquare category to our categories
     */
    private function mapFoursquareCategory($foursquare_category) {
        $mapping = [
            'Restaurant' => 'Entertainment',
            'Bar' => 'Entertainment',
            'Coffee Shop' => 'Entertainment',
            'University' => 'Education',
            'Library' => 'Education',
            'Hospital' => 'Public Services',
            'Government Building' => 'Public Services'
        ];
        
        return $mapping[$foursquare_category] ?? 'Entertainment';
    }
    
    /**
     * Generate description from place data
     */
    private function generateDescription($place) {
        $description = '';
        
        if (isset($place['types']) && is_array($place['types'])) {
            $types = array_map(function($type) {
                return ucfirst(str_replace('_', ' ', $type));
            }, array_slice($place['types'], 0, 3));
            
            $description = 'Un loc minunat în Chișinău care oferă ' . implode(', ', $types) . '.';
        }
        
        if (isset($place['rating']) && $place['rating'] > 4) {
            $description .= ' Foarte apreciat de vizitatori!';
        }
        
        return $description ?: 'Un loc interesant de vizitat în Chișinău.';
    }
    
    /**
     * Generate description for Foursquare places
     */
    private function generateFoursquareDescription($place) {
        $description = 'Un loc popular în Chișinău';
        
        if (isset($place['categories'][0]['name'])) {
            $description .= ' din categoria ' . $place['categories'][0]['name'];
        }
        
        $description .= '.';
        
        if (isset($place['rating']) && $place['rating'] > 8) {
            $description .= ' Foarte bine evaluat de comunitate!';
        }
        
        return $description;
    }
    
    /**
     * Get place photo from Google Places
     */
    private function getPlacePhoto($place) {
        if (isset($place['photos'][0]['photo_reference'])) {
            return GOOGLE_PLACES_API_URL . '/photo?' . http_build_query([
                'photoreference' => $place['photos'][0]['photo_reference'],
                'maxwidth' => 400,
                'key' => GOOGLE_PLACES_API_KEY
            ]);
        }
        
        return '';
    }
    
    /**
     * Remove duplicate places based on name and location similarity
     */
    private function removeDuplicatePlaces($places) {
        $unique = [];
        $seen = [];
        
        foreach ($places as $place) {
            $key = strtolower(trim($place['title']));
            $location_key = round($place['latitude'], 3) . ',' . round($place['longitude'], 3);
            
            if (!isset($seen[$key]) && !isset($seen[$location_key])) {
                $unique[] = $place;
                $seen[$key] = true;
                $seen[$location_key] = true;
            }
        }
        
        return $unique;
    }
    
    /**
     * Make API request with error handling
     */
    private function makeApiRequest($url, $headers = []) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    /**
     * Cache management
     */
    private function getCache($key) {
        $cache_file = CACHE_DIR . $key . '.json';
        
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_DURATION) {
            return json_decode(file_get_contents($cache_file), true);
        }
        
        return null;
    }
    
    private function setCache($key, $data, $duration = null) {
        $cache_file = CACHE_DIR . $key . '.json';
        file_put_contents($cache_file, json_encode($data));
        
        if ($duration) {
            touch($cache_file, time() - (CACHE_DURATION - $duration));
        }
    }
}
?>

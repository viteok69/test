<?php
require_once __DIR__ . '/../config/config.php';

class FreePlacesService {
    private $db;
    private $cache_duration = 3600; // 1 hour
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Get places using Overpass API (OpenStreetMap data) - Completely FREE
     */
    public function getPlacesFromOpenStreetMap($category = '', $radius = 5000) {
        // Chișinău coordinates
        $lat = 47.0105;
        $lng = 28.8638;
        
        $cache_key = 'osm_places_' . md5($category . $radius);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        // Map categories to OpenStreetMap tags
        $osm_queries = $this->getCategoryQueries($category, $lat, $lng, $radius);
        $all_places = [];
        
        foreach ($osm_queries as $query) {
            $places = $this->queryOverpassAPI($query);
            $all_places = array_merge($all_places, $places);
        }
        
        // Process and format the data
        $processed_places = $this->processOSMPlaces($all_places, $category);
        
        $this->setCache($cache_key, $processed_places);
        return $processed_places;
    }
    
    /**
     * Search places using Nominatim (OpenStreetMap search) - FREE
     */
    public function searchPlaces($query, $category = '', $limit = 20) {
        $cache_key = 'search_' . md5($query . $category . $limit);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        // Use Nominatim for search
        $search_url = "https://nominatim.openstreetmap.org/search?" . http_build_query([
            'q' => $query . ' Chișinău Moldova',
            'format' => 'json',
            'limit' => $limit,
            'addressdetails' => 1,
            'extratags' => 1,
            'namedetails' => 1,
            'countrycodes' => 'md', // Moldova only
            'bounded' => 1,
            'viewbox' => '28.7,46.9,29.0,47.1' // Chișinău bounding box
        ]);
        
        $results = $this->makeAPIRequest($search_url);
        
        if ($results) {
            $processed = $this->processNominatimResults($results, $category);
            $this->setCache($cache_key, $processed, 1800); // 30 min cache for searches
            return $processed;
        }
        
        return [];
    }
    
    /**
     * Get recommended places using multiple free sources
     */
    public function getRecommendedPlaces($limit = 6) {
        // Get popular places from different categories
        $entertainment = $this->getPlacesFromOpenStreetMap('Entertainment', 3000);
        $education = $this->getPlacesFromOpenStreetMap('Education', 5000);
        $services = $this->getPlacesFromOpenStreetMap('Public Services', 5000);
        
        // Add some manually curated popular places in Chișinău
        $curated_places = $this->getCuratedPlaces();
        
        $all_places = array_merge($entertainment, $education, $services, $curated_places);
        
        // Sort by importance/popularity
        usort($all_places, function($a, $b) {
            return ($b['importance'] ?? 0) <=> ($a['importance'] ?? 0);
        });
        
        return array_slice($all_places, 0, $limit);
    }
    
    /**
     * Get places from WikiData (free knowledge base)
     */
    public function getPlacesFromWikiData($category = '') {
        $cache_key = 'wikidata_places_' . md5($category);
        $cached_data = $this->getCache($cache_key);
        
        if ($cached_data) {
            return $cached_data;
        }
        
        // SPARQL query for WikiData
        $sparql_query = $this->getWikiDataQuery($category);
        
        $url = "https://query.wikidata.org/sparql?" . http_build_query([
            'query' => $sparql_query,
            'format' => 'json'
        ]);
        
        $results = $this->makeAPIRequest($url, [
            'User-Agent: ChisinauYouthGuide/1.0 (https://example.com/contact)'
        ]);
        
        if ($results && isset($results['results']['bindings'])) {
            $places = $this->processWikiDataResults($results['results']['bindings'], $category);
            $this->setCache($cache_key, $places);
            return $places;
        }
        
        return [];
    }
    
    /**
     * Generate category-specific Overpass API queries
     */
    private function getCategoryQueries($category, $lat, $lng, $radius) {
        $bbox = $this->getBoundingBox($lat, $lng, $radius);
        
        $queries = [];
        
        switch ($category) {
            case 'Entertainment':
                $queries[] = $this->buildOverpassQuery([
                    'amenity' => ['restaurant', 'cafe', 'bar', 'pub', 'fast_food', 'cinema', 'theatre'],
                    'leisure' => ['park', 'playground', 'sports_centre', 'fitness_centre'],
                    'shop' => ['mall', 'department_store']
                ], $bbox);
                break;
                
            case 'Education':
                $queries[] = $this->buildOverpassQuery([
                    'amenity' => ['university', 'college', 'school', 'library', 'language_school'],
                    'building' => ['university', 'school']
                ], $bbox);
                break;
                
            case 'Career':
                $queries[] = $this->buildOverpassQuery([
                    'office' => ['company', 'employment_agency', 'coworking'],
                    'amenity' => ['coworking_space'],
                    'building' => ['office', 'commercial']
                ], $bbox);
                break;
                
            case 'Public Services':
                $queries[] = $this->buildOverpassQuery([
                    'amenity' => ['hospital', 'clinic', 'pharmacy', 'post_office', 'bank', 'police', 'fire_station'],
                    'office' => ['government', 'administrative'],
                    'public_transport' => ['station', 'stop_position']
                ], $bbox);
                break;
                
            default:
                // Get all categories
                $queries[] = $this->buildOverpassQuery([
                    'amenity' => ['restaurant', 'cafe', 'university', 'hospital', 'bank'],
                    'leisure' => ['park', 'sports_centre'],
                    'shop' => ['mall', 'supermarket']
                ], $bbox);
        }
        
        return $queries;
    }
    
    /**
     * Build Overpass API query
     */
    private function buildOverpassQuery($tags, $bbox) {
        $query = "[out:json][timeout:25];\n(\n";
        
        foreach ($tags as $key => $values) {
            foreach ($values as $value) {
                $query .= "  node[\"$key\"=\"$value\"]($bbox);\n";
                $query .= "  way[\"$key\"=\"$value\"]($bbox);\n";
                $query .= "  relation[\"$key\"=\"$value\"]($bbox);\n";
            }
        }
        
        $query .= ");\nout center meta;";
        
        return $query;
    }
    
    /**
     * Query Overpass API
     */
    private function queryOverpassAPI($query) {
        $url = "https://overpass-api.de/api/interpreter";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/plain',
                'User-Agent: ChisinauYouthGuide/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            return $data['elements'] ?? [];
        }
        
        return [];
    }
    
    /**
     * Process OpenStreetMap places
     */
    private function processOSMPlaces($places, $category = '') {
        $processed = [];
        
        foreach ($places as $place) {
            if (!isset($place['tags']['name'])) continue;
            
            $lat = $place['lat'] ?? $place['center']['lat'] ?? 0;
            $lng = $place['lon'] ?? $place['center']['lon'] ?? 0;
            
            if ($lat == 0 || $lng == 0) continue;
            
            $processed[] = [
                'id' => 'osm_' . $place['id'],
                'title' => $place['tags']['name'],
                'description' => $this->generateOSMDescription($place['tags']),
                'category' => $category ?: $this->determineOSMCategory($place['tags']),
                'address' => $this->formatOSMAddress($place['tags']),
                'latitude' => $lat,
                'longitude' => $lng,
                'rating' => 0, // OSM doesn't have ratings
                'image' => $this->getOSMImage($place['tags']),
                'phone' => $place['tags']['phone'] ?? '',
                'website' => $place['tags']['website'] ?? $place['tags']['contact:website'] ?? '',
                'opening_hours' => $place['tags']['opening_hours'] ?? '',
                'source' => 'openstreetmap',
                'external_id' => $place['id'],
                'importance' => $this->calculateImportance($place['tags'])
            ];
        }
        
        return $processed;
    }
    
    /**
     * Process Nominatim search results
     */
    private function processNominatimResults($results, $category = '') {
        $processed = [];
        
        foreach ($results as $result) {
            $processed[] = [
                'id' => 'nominatim_' . $result['osm_id'],
                'title' => $result['display_name'],
                'description' => $this->generateNominatimDescription($result),
                'category' => $category ?: $this->determineNominatimCategory($result),
                'address' => $result['display_name'],
                'latitude' => (float)$result['lat'],
                'longitude' => (float)$result['lon'],
                'rating' => 0,
                'image' => '',
                'phone' => '',
                'website' => '',
                'source' => 'nominatim',
                'external_id' => $result['osm_id'],
                'importance' => (float)($result['importance'] ?? 0)
            ];
        }
        
        return $processed;
    }
    
    /**
     * Get WikiData query for places in Chișinău
     */
    private function getWikiDataQuery($category) {
        $base_query = '
        SELECT DISTINCT ?place ?placeLabel ?coord ?image ?website ?phone WHERE {
          ?place wdt:P131* wd:Q21197 .  # Located in Chișinău
          ?place wdt:P625 ?coord .      # Has coordinates
        ';
        
        switch ($category) {
            case 'Education':
                $base_query .= '
                  { ?place wdt:P31/wdt:P279* wd:Q3918 } UNION    # University
                  { ?place wdt:P31/wdt:P279* wd:Q9842 } UNION    # Primary school
                  { ?place wdt:P31/wdt:P279* wd:Q875538 }        # University
                ';
                break;
            case 'Entertainment':
                $base_query .= '
                  { ?place wdt:P31/wdt:P279* wd:Q11707 } UNION   # Restaurant
                  { ?place wdt:P31/wdt:P279* wd:Q41253 } UNION   # Movie theater
                  { ?place wdt:P31/wdt:P279* wd:Q22698 }         # Park
                ';
                break;
            default:
                $base_query .= '
                  { ?place wdt:P31/wdt:P279* wd:Q3918 } UNION    # University
                  { ?place wdt:P31/wdt:P279* wd:Q11707 } UNION   # Restaurant
                  { ?place wdt:P31/wdt:P279* wd:Q22698 }         # Park
                ';
        }
        
        $base_query .= '
          OPTIONAL { ?place wdt:P18 ?image }
          OPTIONAL { ?place wdt:P856 ?website }
          OPTIONAL { ?place wdt:P1329 ?phone }
          SERVICE wikibase:label { bd:serviceParam wikibase:language "ro,en" }
        }
        LIMIT 50
        ';
        
        return $base_query;
    }
    
    /**
     * Process WikiData results
     */
    private function processWikiDataResults($results, $category) {
        $processed = [];
        
        foreach ($results as $result) {
            if (!isset($result['coord'])) continue;
            
            // Parse coordinates from WikiData format
            preg_match('/Point$$([0-9.-]+) ([0-9.-]+)$$/', $result['coord']['value'], $matches);
            if (count($matches) < 3) continue;
            
            $lng = (float)$matches[1];
            $lat = (float)$matches[2];
            
            $processed[] = [
                'id' => 'wikidata_' . basename($result['place']['value']),
                'title' => $result['placeLabel']['value'],
                'description' => 'Loc important în Chișinău cu informații verificate din WikiData.',
                'category' => $category ?: 'Entertainment',
                'address' => 'Chișinău, Moldova',
                'latitude' => $lat,
                'longitude' => $lng,
                'rating' => 0,
                'image' => $result['image']['value'] ?? '',
                'phone' => $result['phone']['value'] ?? '',
                'website' => $result['website']['value'] ?? '',
                'source' => 'wikidata',
                'external_id' => basename($result['place']['value']),
                'importance' => 0.8 // WikiData places are generally important
            ];
        }
        
        return $processed;
    }
    
    /**
     * Get manually curated places (backup data)
     */
    private function getCuratedPlaces() {
        return [
            [
                'id' => 'curated_1',
                'title' => 'Parcul Central "Ștefan cel Mare"',
                'description' => 'Cel mai mare și popular parc din centrul Chișinăului, perfect pentru plimbări și relaxare.',
                'category' => 'Entertainment',
                'address' => 'Bulevardul Ștefan cel Mare și Sfânt, Chișinău',
                'latitude' => 47.0245,
                'longitude' => 28.8322,
                'rating' => 4.5,
                'image' => '/placeholder.svg?height=300&width=400',
                'phone' => '',
                'website' => '',
                'source' => 'curated',
                'importance' => 1.0
            ],
            [
                'id' => 'curated_2',
                'title' => 'Universitatea de Stat din Moldova',
                'description' => 'Cea mai prestigioasă universitate din Moldova, oferind o gamă largă de programe de studii.',
                'category' => 'Education',
                'address' => 'Strada Alexei Mateevici 60, Chișinău',
                'latitude' => 47.0220,
                'longitude' => 28.8353,
                'rating' => 4.2,
                'image' => '/placeholder.svg?height=300&width=400',
                'phone' => '+373 22 577-102',
                'website' => 'https://usm.md',
                'source' => 'curated',
                'importance' => 0.9
            ],
            [
                'id' => 'curated_3',
                'title' => 'Mall Dova',
                'description' => 'Centru comercial modern cu magazine, restaurante și cinema.',
                'category' => 'Entertainment',
                'address' => 'Strada Arborilor 21, Chișinău',
                'latitude' => 47.0186,
                'longitude' => 28.8067,
                'rating' => 4.1,
                'image' => '/placeholder.svg?height=300&width=400',
                'phone' => '+373 22 888-999',
                'website' => 'https://malldova.md',
                'source' => 'curated',
                'importance' => 0.8
            ],
            [
                'id' => 'curated_4',
                'title' => 'Spitalul Clinic Republican',
                'description' => 'Principala instituție medicală din Moldova, oferind servicii medicale de înaltă calitate.',
                'category' => 'Public Services',
                'address' => 'Strada Testemițanu 29, Chișinău',
                'latitude' => 47.0351,
                'longitude' => 28.8186,
                'rating' => 3.8,
                'image' => '/placeholder.svg?height=300&width=400',
                'phone' => '+373 22 205-205',
                'website' => 'https://scr.md',
                'source' => 'curated',
                'importance' => 0.9
            ]
        ];
    }
    
    /**
     * Helper functions
     */
    private function getBoundingBox($lat, $lng, $radius) {
        // Convert radius from meters to degrees (approximate)
        $lat_offset = $radius / 111000; // 1 degree ≈ 111km
        $lng_offset = $radius / (111000 * cos(deg2rad($lat)));
        
        $south = $lat - $lat_offset;
        $west = $lng - $lng_offset;
        $north = $lat + $lat_offset;
        $east = $lng + $lng_offset;
        
        return "$south,$west,$north,$east";
    }
    
    private function determineOSMCategory($tags) {
        if (isset($tags['amenity'])) {
            $amenity = $tags['amenity'];
            if (in_array($amenity, ['restaurant', 'cafe', 'bar', 'pub', 'cinema', 'theatre'])) {
                return 'Entertainment';
            }
            if (in_array($amenity, ['university', 'college', 'school', 'library'])) {
                return 'Education';
            }
            if (in_array($amenity, ['hospital', 'clinic', 'pharmacy', 'post_office', 'bank', 'police'])) {
                return 'Public Services';
            }
        }
        
        if (isset($tags['office'])) {
            return 'Career';
        }
        
        return 'Entertainment';
    }
    
    private function determineNominatimCategory($result) {
        $class = $result['class'] ?? '';
        $type = $result['type'] ?? '';
        
        if ($class === 'amenity') {
            if (in_array($type, ['restaurant', 'cafe', 'bar', 'pub', 'cinema', 'theatre'])) {
                return 'Entertainment';
            }
            if (in_array($type, ['university', 'college', 'school', 'library'])) {
                return 'Education';
            }
            if (in_array($type, ['hospital', 'clinic', 'pharmacy', 'post_office', 'bank'])) {
                return 'Public Services';
            }
        }
        
        return 'Entertainment';
    }
    
    private function generateOSMDescription($tags) {
        $description = 'Un loc interesant în Chișinău';
        
        if (isset($tags['amenity'])) {
            $amenity_names = [
                'restaurant' => 'restaurant',
                'cafe' => 'cafenea',
                'university' => 'universitate',
                'hospital' => 'spital',
                'bank' => 'bancă',
                'park' => 'parc'
            ];
            
            $amenity = $tags['amenity'];
            if (isset($amenity_names[$amenity])) {
                $description = 'O ' . $amenity_names[$amenity] . ' populară în Chișinău';
            }
        }
        
        if (isset($tags['description'])) {
            $description .= '. ' . $tags['description'];
        }
        
        return $description . '.';
    }
    
    private function generateNominatimDescription($result) {
        $type = $result['type'] ?? '';
        $class = $result['class'] ?? '';
        
        return "Un $type din categoria $class, localizat în Chișinău.";
    }
    
    private function formatOSMAddress($tags) {
        $address_parts = [];
        
        if (isset($tags['addr:street'])) {
            $address_parts[] = $tags['addr:street'];
        }
        if (isset($tags['addr:housenumber'])) {
            $address_parts[] = $tags['addr:housenumber'];
        }
        
        $address = implode(' ', $address_parts);
        
        if (empty($address) && isset($tags['name'])) {
            $address = $tags['name'] . ', Chișinău';
        }
        
        return $address ?: 'Chișinău, Moldova';
    }
    
    private function getOSMImage($tags) {
        // OSM doesn't have images, but we can use Wikimedia Commons if available
        if (isset($tags['wikimedia_commons'])) {
            return "https://commons.wikimedia.org/wiki/File:" . $tags['wikimedia_commons'];
        }
        
        return '/placeholder.svg?height=300&width=400';
    }
    
    private function calculateImportance($tags) {
        $importance = 0.5; // Base importance
        
        // Increase importance based on tags
        if (isset($tags['tourism'])) $importance += 0.3;
        if (isset($tags['historic'])) $importance += 0.2;
        if (isset($tags['amenity']) && in_array($tags['amenity'], ['university', 'hospital', 'restaurant'])) {
            $importance += 0.2;
        }
        if (isset($tags['wikipedia'])) $importance += 0.1;
        
        return min(1.0, $importance);
    }
    
    private function makeAPIRequest($url, $headers = []) {
        $default_headers = [
            'User-Agent: ChisinauYouthGuide/1.0 (contact@example.com)'
        ];
        
        $headers = array_merge($default_headers, $headers);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            return json_decode($response, true);
        }
        
        return null;
    }
    
    private function getCache($key) {
        $cache_file = __DIR__ . '/../cache/' . $key . '.json';
        
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $this->cache_duration) {
            return json_decode(file_get_contents($cache_file), true);
        }
        
        return null;
    }
    
    private function setCache($key, $data, $duration = null) {
        $cache_dir = __DIR__ . '/../cache/';
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        $cache_file = $cache_dir . $key . '.json';
        file_put_contents($cache_file, json_encode($data));
        
        if ($duration) {
            touch($cache_file, time() - ($this->cache_duration - $duration));
        }
    }
}
?>

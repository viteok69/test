<?php
require_once '../config/config.php';
require_once '../services/FreePlacesService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    $limit = isset($_GET['limit']) ? min(50, (int)$_GET['limit']) : 20;
    
    if (strlen($query) < 2) {
        echo json_encode(['results' => [], 'message' => 'Query too short']);
        exit;
    }
    
    $placesService = new FreePlacesService();
    $results = $placesService->searchPlaces($query, $category, $limit);
    
    // Format results for frontend
    $formatted_results = array_map(function($place) {
        return [
            'id' => $place['id'],
            'title' => $place['title'],
            'description' => substr($place['description'], 0, 150) . '...',
            'category' => $place['category'],
            'address' => $place['address'],
            'importance' => $place['importance'] ?? 0,
            'image' => $place['image'],
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'source' => $place['source']
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'results' => $formatted_results,
        'count' => count($formatted_results),
        'sources' => ['OpenStreetMap', 'WikiData', 'Nominatim'],
        'cost' => 'FREE'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'A apărut o eroare în căutare.',
        'debug' => $e->getMessage()
    ]);
}
?>

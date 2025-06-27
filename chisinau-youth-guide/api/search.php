<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/helpers.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get search parameters
    $query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
    $category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    // Validate limit
    $limit = max(1, min(50, $limit));
    
    if (empty($query) && empty($category)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nu s-au furnizat criterii de căutare.',
            'results' => []
        ]);
        exit;
    }
    
    // Build search query for database only
    $where_conditions = [];
    $params = [];
    
    if (!empty($query)) {
        // Search in name, description, and address
        $where_conditions[] = "(name LIKE :search OR description LIKE :search OR address LIKE :search)";
        $params[':search'] = '%' . $query . '%';
    }
    
    if (!empty($category)) {
        $where_conditions[] = "category = :category";
        $params[':category'] = $category;
    }
    
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Execute search query on places table
    $sql = "SELECT id, name, category, address, description, website_url, phone, rating, price_range, opening_hours 
            FROM places 
            $where_clause 
            ORDER BY 
                CASE WHEN name LIKE :exact_match THEN 1 ELSE 2 END,
                rating DESC, 
                name ASC 
            LIMIT :limit";
    
    $stmt = $db->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    if (!empty($query)) {
        $stmt->bindValue(':exact_match', $query . '%', PDO::PARAM_STR);
    } else {
        $stmt->bindValue(':exact_match', '', PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Format results
    $formatted_results = [];
    foreach ($results as $place) {
        $formatted_results[] = [
            'id' => (int)$place['id'],
            'name' => $place['name'],
            'category' => $place['category'],
            'address' => $place['address'],
            'description' => substr($place['description'], 0, 150) . (strlen($place['description']) > 150 ? '...' : ''),
            'website_url' => $place['website_url'],
            'phone' => $place['phone'],
            'rating' => (float)$place['rating'],
            'price_range' => $place['price_range'],
            'opening_hours' => $place['opening_hours']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'category' => $category,
        'total_results' => count($formatted_results),
        'results' => $formatted_results
    ]);
    
} catch (Exception $e) {
    error_log("Search API error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Nu s-au găsit rezultate pentru căutarea dumneavoastră.',
        'error_details' => $e->getMessage(),
        'results' => []
    ]);
}
?>

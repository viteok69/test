<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? min(50, (int)$_GET['limit']) : 20;
    
    if (strlen($query) < 2) {
        echo json_encode([
            'success' => true,
            'results' => [],
            'message' => 'Introduceți cel puțin 2 caractere pentru căutare'
        ]);
        exit;
    }
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Search in local database first
    $local_results = searchLocalPlaces($db, $query, $limit);
    
    // If we have enough local results, return them
    if (count($local_results) >= 5) {
        echo json_encode([
            'success' => true,
            'results' => $local_results,
            'source' => 'local_database'
        ]);
        exit;
    }
    
    // Add some static results for common searches
    $static_results = getStaticResults($query);
    $all_results = array_merge($local_results, $static_results);
    
    // Limit results
    $all_results = array_slice($all_results, 0, $limit);
    
    echo json_encode([
        'success' => true,
        'results' => $all_results,
        'source' => 'mixed'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Eroare la căutare: ' . $e->getMessage()
    ]);
}

function searchLocalPlaces($db, $query, $limit) {
    try {
        $sql = "SELECT p.*, c.name_ro as category_name 
                FROM places p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE (p.title LIKE :query OR p.description LIKE :query OR p.address LIKE :query)
                AND p.is_active = 1 
                ORDER BY p.rating DESC, p.title ASC 
                LIMIT :limit";
        
        $stmt = $db->prepare($sql);
        $search_term = '%' . $query . '%';
        $stmt->bindParam(':query', $search_term, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'id' => 'local_' . $row['id'],
                'title' => $row['title'],
                'description' => substr($row['description'], 0, 150) . '...',
                'category' => $row['category_name'] ?: 'General',
                'address' => $row['address'],
                'rating' => (float)$row['rating'],
                'image' => $row['image'] ?: '/placeholder.svg?height=200&width=300'
            ];
        }
        
        return $results;
        
    } catch (Exception $e) {
        return [];
    }
}

function getStaticResults($query) {
    $static_places = [
        // Entertainment
        [
            'id' => 'static_1',
            'title' => 'Parcul Central "Ștefan cel Mare"',
            'description' => 'Cel mai mare parc din centrul Chișinăului, perfect pentru plimbări și relaxare.',
            'category' => 'Entertainment',
            'address' => 'Bulevardul Ștefan cel Mare, Chișinău',
            'rating' => 4.5,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['parc', 'park', 'stefan', 'central', 'plimbare', 'relaxare']
        ],
        [
            'id' => 'static_2',
            'title' => 'Mall Dova',
            'description' => 'Centru comercial modern cu magazine, restaurante și cinema.',
            'category' => 'Entertainment',
            'address' => 'Strada Arborilor 21, Chișinău',
            'rating' => 4.1,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['mall', 'shopping', 'magazine', 'cinema', 'restaurant', 'cumparaturi']
        ],
        [
            'id' => 'static_3',
            'title' => 'Teatrul Național "Mihai Eminescu"',
            'description' => 'Teatrul principal din Moldova cu spectacole de calitate.',
            'category' => 'Entertainment',
            'address' => 'Bulevardul Ștefan cel Mare 79, Chișinău',
            'rating' => 4.3,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['teatru', 'theater', 'spectacol', 'cultura', 'eminescu']
        ],
        
        // Education
        [
            'id' => 'static_4',
            'title' => 'Universitatea de Stat din Moldova',
            'description' => 'Cea mai prestigioasă universitate din Moldova.',
            'category' => 'Education',
            'address' => 'Strada Alexei Mateevici 60, Chișinău',
            'rating' => 4.2,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['universitate', 'university', 'usm', 'studii', 'educatie', 'facultate']
        ],
        [
            'id' => 'static_5',
            'title' => 'Universitatea Tehnică din Moldova',
            'description' => 'Universitate tehnică de top pentru inginerie și IT.',
            'category' => 'Education',
            'address' => 'Bulevardul Ștefan cel Mare 168, Chișinău',
            'rating' => 4.0,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['universitate', 'tehnica', 'utm', 'inginerie', 'it', 'tehnologie']
        ],
        
        // Career
        [
            'id' => 'static_6',
            'title' => 'Agenția Națională pentru Ocuparea Forței de Muncă',
            'description' => 'Servicii pentru găsirea unui loc de muncă și consiliere profesională.',
            'category' => 'Career',
            'address' => 'Strada Vasile Alecsandri 42, Chișinău',
            'rating' => 3.5,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['munca', 'job', 'angajare', 'cariera', 'consiliere', 'profesional']
        ],
        
        // Public Services
        [
            'id' => 'static_7',
            'title' => 'Spitalul Clinic Republican',
            'description' => 'Principala instituție medicală din Moldova.',
            'category' => 'Public Services',
            'address' => 'Strada Testemițanu 29, Chișinău',
            'rating' => 3.8,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['spital', 'hospital', 'medical', 'sanatate', 'doctor', 'tratament']
        ],
        [
            'id' => 'static_8',
            'title' => 'Primăria Municipiului Chișinău',
            'description' => 'Servicii administrative și publice pentru cetățeni.',
            'category' => 'Public Services',
            'address' => 'Strada Ștefan cel Mare 81, Chișinău',
            'rating' => 3.2,
            'image' => '/placeholder.svg?height=200&width=300',
            'keywords' => ['primarie', 'servicii', 'administrative', 'documente', 'cetaten']
        ]
    ];
    
    $results = [];
    $query_lower = mb_strtolower($query, 'UTF-8');
    
    foreach ($static_places as $place) {
        // Check if query matches title, description, or keywords
        $title_match = mb_strpos(mb_strtolower($place['title'], 'UTF-8'), $query_lower) !== false;
        $desc_match = mb_strpos(mb_strtolower($place['description'], 'UTF-8'), $query_lower) !== false;
        $keyword_match = false;
        
        foreach ($place['keywords'] as $keyword) {
            if (mb_strpos(mb_strtolower($keyword, 'UTF-8'), $query_lower) !== false || 
                mb_strpos($query_lower, mb_strtolower($keyword, 'UTF-8')) !== false) {
                $keyword_match = true;
                break;
            }
        }
        
        if ($title_match || $desc_match || $keyword_match) {
            unset($place['keywords']); // Remove keywords from result
            $results[] = $place;
        }
    }
    
    return $results;
}
?>

<?php
require_once '../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $limit = isset($_GET['limit']) ? min(50, (int)$_GET['limit']) : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Build query
    $where_clause = "WHERE p.is_active = 1";
    $params = [];
    
    if ($category > 0) {
        $where_clause .= " AND p.category_id = :category";
        $params[':category'] = $category;
    }
    
    // Get places from database
    $sql = "SELECT p.*, c.name_ro as category_name, c.color as category_color, c.icon as category_icon
            FROM places p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where_clause
            ORDER BY p.rating DESC, p.title ASC 
            LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $places = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $places[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category_name'] ?: 'General',
            'category_color' => $row['category_color'] ?: '#007bff',
            'category_icon' => $row['category_icon'] ?: 'fas fa-map-marker-alt',
            'address' => $row['address'],
            'latitude' => (float)$row['latitude'],
            'longitude' => (float)$row['longitude'],
            'rating' => (float)$row['rating'],
            'image' => $row['image'] ?: '/placeholder.svg?height=200&width=300',
            'phone' => $row['phone'],
            'website' => $row['website'],
            'opening_hours' => $row['opening_hours']
        ];
    }
    
    // If no places in database, return static data
    if (empty($places)) {
        $places = getStaticPlaces($category);
    }
    
    echo json_encode([
        'success' => true,
        'places' => $places,
        'count' => count($places),
        'has_more' => count($places) === $limit
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Eroare la încărcarea locurilor: ' . $e->getMessage()
    ]);
}

function getStaticPlaces($category = 0) {
    $all_places = [
        // Entertainment places
        [
            'id' => 'static_1',
            'title' => 'Parcul Central "Ștefan cel Mare"',
            'description' => 'Cel mai mare parc din centrul Chișinăului, perfect pentru plimbări și relaxare. Aici găsești alei frumoase, bănci pentru odihnă și multe activități în aer liber.',
            'category' => 'Entertainment',
            'category_color' => '#e74c3c',
            'category_icon' => 'fas fa-gamepad',
            'address' => 'Bulevardul Ștefan cel Mare, Chișinău',
            'latitude' => 47.0245,
            'longitude' => 28.8322,
            'rating' => 4.5,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '',
            'website' => '',
            'opening_hours' => '24/7',
            'category_id' => 1
        ],
        [
            'id' => 'static_2',
            'title' => 'Mall Dova',
            'description' => 'Centru comercial modern cu peste 100 de magazine, restaurante, cinema și zone de divertisment pentru tineri.',
            'category' => 'Entertainment',
            'category_color' => '#e74c3c',
            'category_icon' => 'fas fa-gamepad',
            'address' => 'Strada Arborilor 21, Chișinău',
            'latitude' => 47.0186,
            'longitude' => 28.8067,
            'rating' => 4.1,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '+373 22 888-999',
            'website' => 'https://malldova.md',
            'opening_hours' => '10:00-22:00',
            'category_id' => 1
        ],
        
        // Education places
        [
            'id' => 'static_3',
            'title' => 'Universitatea de Stat din Moldova',
            'description' => 'Cea mai prestigioasă universitate din Moldova, oferind programe de licență, masterat și doctorat în diverse domenii.',
            'category' => 'Education',
            'category_color' => '#3498db',
            'category_icon' => 'fas fa-graduation-cap',
            'address' => 'Strada Alexei Mateevici 60, Chișinău',
            'latitude' => 47.0220,
            'longitude' => 28.8353,
            'rating' => 4.2,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '+373 22 577-102',
            'website' => 'https://usm.md',
            'opening_hours' => '08:00-18:00',
            'category_id' => 2
        ],
        [
            'id' => 'static_4',
            'title' => 'Universitatea Tehnică din Moldova',
            'description' => 'Universitate de top pentru inginerie, IT și tehnologii moderne. Oferă programe actuale și laboratoare moderne.',
            'category' => 'Education',
            'category_color' => '#3498db',
            'category_icon' => 'fas fa-graduation-cap',
            'address' => 'Bulevardul Ștefan cel Mare 168, Chișinău',
            'latitude' => 47.0156,
            'longitude' => 28.8350,
            'rating' => 4.0,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '+373 22 509-999',
            'website' => 'https://utm.md',
            'opening_hours' => '08:00-18:00',
            'category_id' => 2
        ],
        
        // Career places
        [
            'id' => 'static_5',
            'title' => 'Agenția Națională pentru Ocuparea Forței de Muncă',
            'description' => 'Servicii gratuite pentru găsirea unui loc de muncă, consiliere profesională și cursuri de calificare.',
            'category' => 'Career',
            'category_color' => '#f39c12',
            'category_icon' => 'fas fa-briefcase',
            'address' => 'Strada Vasile Alecsandri 42, Chișinău',
            'latitude' => 47.0267,
            'longitude' => 28.8341,
            'rating' => 3.5,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '+373 22 25-15-07',
            'website' => 'https://anofm.md',
            'opening_hours' => '08:00-17:00',
            'category_id' => 3
        ],
        
        // Public Services
        [
            'id' => 'static_6',
            'title' => 'Spitalul Clinic Republican',
            'description' => 'Principala instituție medicală din Moldova, oferind servicii medicale de înaltă calitate și urgențe 24/7.',
            'category' => 'Public Services',
            'category_color' => '#27ae60',
            'category_icon' => 'fas fa-hospital',
            'address' => 'Strada Testemițanu 29, Chișinău',
            'latitude' => 47.0351,
            'longitude' => 28.8186,
            'rating' => 3.8,
            'image' => '/placeholder.svg?height=200&width=300',
            'phone' => '+373 22 205-205',
            'website' => 'https://scr.md',
            'opening_hours' => '24/7',
            'category_id' => 4
        ]
    ];
    
    // Filter by category if specified
    if ($category > 0) {
        return array_filter($all_places, function($place) use ($category) {
            return $place['category_id'] == $category;
        });
    }
    
    return $all_places;
}
?>

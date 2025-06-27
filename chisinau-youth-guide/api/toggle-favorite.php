<?php
require_once '../config/config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii conectat pentru a salva favorite.']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodă nepermisă.']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['place_id']) || !is_numeric($input['place_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID loc invalid.']);
    exit;
}

$place_id = (int)$input['place_id'];
$user_id = $_SESSION['user_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if place exists
    $place_query = "SELECT id, title FROM places WHERE id = :place_id AND is_active = 1";
    $place_stmt = $db->prepare($place_query);
    $place_stmt->bindParam(':place_id', $place_id);
    $place_stmt->execute();
    
    if ($place_stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Locul nu a fost găsit.']);
        exit;
    }
    
    $place = $place_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if already favorited
    $check_query = "SELECT id FROM user_favorites WHERE user_id = :user_id AND place_id = :place_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->bindParam(':place_id', $place_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Remove from favorites
        $delete_query = "DELETE FROM user_favorites WHERE user_id = :user_id AND place_id = :place_id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->bindParam(':place_id', $place_id);
        $delete_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'is_favorite' => false,
            'message' => 'Locul "' . $place['title'] . '" a fost eliminat din favorite.'
        ]);
    } else {
        // Add to favorites
        $insert_query = "INSERT INTO user_favorites (user_id, place_id) VALUES (:user_id, :place_id)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id);
        $insert_stmt->bindParam(':place_id', $place_id);
        $insert_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Locul "' . $place['title'] . '" a fost adăugat la favorite.'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A apărut o eroare. Te rugăm să încerci din nou.']);
}
?>

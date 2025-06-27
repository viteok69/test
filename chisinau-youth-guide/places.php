<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = "category = :category";
    $params[':category'] = $category_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(name LIKE :search OR description LIKE :search OR address LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get places
$query = "SELECT * FROM places $where_clause ORDER BY category, name";
$stmt = $db->prepare($query);
$stmt->execute($params);
$places = $stmt->fetchAll();

// Group places by category
$grouped_places = [];
foreach ($places as $place) {
    $grouped_places[$place['category']][] = $place;
}

// Get all categories for filter
$categories_query = "SELECT DISTINCT category FROM places ORDER BY category";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Category display names
$category_names = [
    'park' => '🌳 Parcuri',
    'restaurant' => '🍽️ Restaurante', 
    'cafe' => '☕ Cafenele',
    'museum' => '🏛️ Muzee',
    'shopping' => '🛍️ Shopping',
    'education' => '🎓 Educație',
    'entertainment' => '🎬 Divertisment',
    'sports' => '⚽ Sport',
    'health' => '🏥 Sănătate',
    'transport' => '🚌 Transport',
    'culture' => '🎭 Cultură',
    'nightlife' => '🌙 Viața de noapte'
];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locuri în Chișinău - Ghidul Tinerilor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #ec4899;
            --accent-color: #10b981;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .main-content {
            background: white;
            border-radius: 20px;
            margin: 2rem 0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .search-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
        }
        
        .place-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .place-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .category-header {
            background: linear-gradient(135deg, var(--accent-color), #059669);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin: 2rem 0 1rem 0;
        }
        
        .btn-modern {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .price-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
        }
        
        .price-free { background: #dcfce7; color: #166534; }
        .price-budget { background: #fef3c7; color: #92400e; }
        .price-moderate { background: #dbeafe; color: #1e40af; }
        .price-expensive { background: #fce7f3; color: #be185d; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="places.php">
                <i class="fas fa-map-marked-alt text-primary"></i>
                Ghidul Tinerilor Chișinău
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="places.php">Toate Locurile</a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-cog"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-user"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <div class="main-content">
            <!-- Search Section -->
            <div class="search-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <h1 class="text-center mb-4">
                                <i class="fas fa-map-marker-alt"></i>
                                Descoperă Chișinăul
                            </h1>
                            <p class="text-center mb-4 opacity-90">
                                Găsește cele mai bune locuri pentru tineri în capitala Moldovei
                            </p>
                            
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-lg" 
                                           name="search" placeholder="Caută locuri..." 
                                           value="<?php echo htmlspecialchars($search_query); ?>">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select form-select-lg" name="category">
                                        <option value="">Toate categoriile</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" 
                                                    <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                                <?php echo $category_names[$cat] ?? ucfirst($cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-light btn-lg w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Places Content -->
            <div class="container py-4">
                <?php if (empty($places)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">Nu s-au găsit locuri</h3>
                        <p class="text-muted">Încearcă să modifici criteriile de căutare.</p>
                        <a href="places.php" class="btn btn-primary-modern btn-modern">
                            <i class="fas fa-list"></i> Vezi Toate Locurile
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($grouped_places as $category => $category_places): ?>
                        <div class="category-header">
                            <h2 class="mb-0">
                                <?php echo $category_names[$category] ?? ucfirst($category); ?>
                                <span class="badge bg-light text-dark ms-2"><?php echo count($category_places); ?></span>
                            </h2>
                        </div>
                        
                        <div class="row">
                            <?php foreach ($category_places as $place): ?>
                                <div class="col-lg-6 col-xl-4">
                                    <div class="card place-card h-100">
                                        <?php if ($place['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($place['image_url']); ?>" 
                                                 class="card-img-top" style="height: 200px; object-fit: cover;"
                                                 alt="<?php echo htmlspecialchars($place['name']); ?>">
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="card-title mb-0">
                                                    <?php echo htmlspecialchars($place['name']); ?>
                                                </h5>
                                                <span class="price-badge price-<?php echo $place['price_range']; ?>">
                                                    <?php 
                                                    $price_labels = [
                                                        'free' => 'Gratuit',
                                                        'budget' => 'Ieftin', 
                                                        'moderate' => 'Moderat',
                                                        'expensive' => 'Scump'
                                                    ];
                                                    echo $price_labels[$place['price_range']] ?? 'N/A';
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <p class="card-text text-muted small mb-2">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($place['address']); ?>
                                            </p>
                                            
                                            <p class="card-text">
                                                <?php echo htmlspecialchars(substr($place['description'], 0, 120)) . '...'; ?>
                                            </p>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <?php if ($place['phone']): ?>
                                                    <a href="tel:<?php echo $place['phone']; ?>" 
                                                       class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($place['website']): ?>
                                                    <a href="<?php echo htmlspecialchars($place['website']); ?>" 
                                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($place['opening_hours']): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i>
                                                        <?php echo htmlspecialchars($place['opening_hours']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

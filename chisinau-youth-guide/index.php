<?php
session_start();
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Get search and filter parameters
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Build query conditions
$where_conditions = [];
$params = [];

if (!empty($search_query)) {
    $where_conditions[] = "(name LIKE :search OR description LIKE :search OR address LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($category_filter)) {
    $where_conditions[] = "category = :category";
    $params[':category'] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get places
try {
    $query = "SELECT * FROM places $where_clause ORDER BY category, rating DESC, name";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $places = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Error fetching places: " . $e->getMessage());
    $places = [];
}

// Group places by category
$grouped_places = [];
foreach ($places as $place) {
    $grouped_places[$place['category']][] = $place;
}

// Get all categories for filter dropdown
try {
    $categories_query = "SELECT DISTINCT category FROM places ORDER BY category";
    $categories_stmt = $db->prepare($categories_query);
    $categories_stmt->execute();
    $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Category display names with emojis
$category_names = [
    'park' => '🌳 Parcuri și Recreere',
    'restaurant' => '🍽️ Restaurante', 
    'cafe' => '☕ Cafenele',
    'museum' => '🏛️ Muzee și Cultură',
    'shopping' => '🛍️ Shopping',
    'education' => '🎓 Educație',
    'entertainment' => '🎬 Divertisment',
    'sports' => '⚽ Sport și Fitness',
    'coworking' => '💻 Spații de Lucru',
    'nightlife' => '🌙 Viața de Noapte',
    'health' => '🏥 Sănătate',
    'transport' => '🚌 Transport'
];

// Get total count for display
$total_places = count($places);
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghidul Tinerilor Chișinău - Descoperă Orașul</title>
    <meta name="description" content="Ghidul complet pentru tinerii din Chișinău. Descoperă cele mai bune locuri, restaurante, cafenele, parcuri și activități din capitala Moldovei.">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-container">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-map-marked-alt"></i>
                    Chișinău Guide
                </a>
                
                <ul class="navbar-nav" id="navbar-nav">
                    <li><a href="index.php" class="nav-link active">Acasă</a></li>
                    <li><a href="admin.php" class="nav-link">Admin</a></li>
                    <li><a href="#" class="nav-link">Contact</a></li>
                </ul>

                <button class="theme-toggle" onclick="toggleTheme()" title="Schimbă tema">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>

                <button class="navbar-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Descoperă Chișinăul</h1>
            <p>Ghidul tău complet pentru cele mai bune locuri din capitala Moldovei</p>
        </div>
    </section>

    <!-- Search Section -->
    <div class="container">
        <div class="search-section">
            <form method="GET" class="search-form" id="search-form">
                <div class="form-group">
                    <label for="search" class="form-label">Caută locuri</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        class="form-input" 
                        placeholder="Caută după nume, descriere sau adresă..."
                        value="<?php echo htmlspecialchars($search_query); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Categorie</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">Toate categoriile</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                                <?php echo $category_names[$category] ?? ucfirst($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i>
                    Caută
                </button>
            </form>

            <?php if (!empty($search_query) || !empty($category_filter)): ?>
                <div class="py-4">
                    <p class="text-center">
                        <strong><?php echo $total_places; ?></strong> 
                        <?php echo $total_places === 1 ? 'loc găsit' : 'locuri găsite'; ?>
                        <?php if (!empty($search_query)): ?>
                            pentru "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                        <?php endif; ?>
                        <?php if (!empty($category_filter)): ?>
                            în categoria "<strong><?php echo $category_names[$category_filter] ?? ucfirst($category_filter); ?></strong>"
                        <?php endif; ?>
                    </p>
                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline btn-sm">
                            <i class="fas fa-times"></i>
                            Șterge filtrele
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Places Content -->
    <main class="container py-8">
        <?php if (empty($places)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Nu am găsit rezultate</h3>
                <p>Încearcă să modifici termenii de căutare sau să alegi o altă categorie.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Vezi toate locurile
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_places as $category => $category_places): ?>
                <section class="fade-in">
                    <div class="category-header">
                        <h2><?php echo $category_names[$category] ?? ucfirst($category); ?></h2>
                    </div>
                    
                    <div class="grid grid-cols-3 mb-8">
                        <?php foreach ($category_places as $place): ?>
                            <div class="card place-card">
                                <?php if (!empty($place['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($place['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($place['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="place-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">
                                            <?php echo htmlspecialchars($place['name']); ?>
                                        </h3>
                                        <?php if ($place['rating'] > 0): ?>
                                            <div class="flex items-center gap-1">
                                                <i class="fas fa-star text-yellow-500"></i>
                                                <span class="font-semibold"><?php echo $place['rating']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        <span class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($place['address']); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-4 line-clamp-3">
                                        <?php echo htmlspecialchars(substr($place['description'], 0, 150)); ?>
                                        <?php echo strlen($place['description']) > 150 ? '...' : ''; ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="badge price-<?php echo $place['price_range']; ?>">
                                            <?php 
                                            $price_labels = [
                                                'free' => 'Gratuit',
                                                'budget' => 'Buget mic',
                                                'moderate' => 'Moderat', 
                                                'expensive' => 'Scump'
                                            ];
                                            echo $price_labels[$place['price_range']] ?? 'N/A';
                                            ?>
                                        </span>
                                        
                                        <div class="flex gap-2">
                                            <?php if (!empty($place['phone'])): ?>
                                                <a href="tel:<?php echo $place['phone']; ?>" 
                                                   class="btn btn-ghost btn-sm" title="Sună">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($place['website_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($place['website_url']); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-ghost btn-sm" title="Vizitează site-ul">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($place['website_url'])): ?>
                                        <div class="mb-3">
                                            <a href="<?php echo htmlspecialchars($place['website_url']); ?>" 
                                               target="_blank" 
                                               class="btn btn-outline btn-sm w-full">
                                                <i class="fas fa-globe"></i>
                                                Vizitează site-ul
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($place['opening_hours'])): ?>
                                        <div class="pt-3 border-t border-gray-200">
                                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo htmlspecialchars($place['opening_hours']); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="py-12" style="background: var(--gray-900); color: var(--white);">
        <div class="container text-center">
            <div class="mb-6">
                <h3 class="text-2xl font-bold mb-2" style="color: var(--white);">Ghidul Tinerilor Chișinău</h3>
                <p style="color: var(--gray-300);">Descoperă cele mai bune locuri din capitala Moldovei</p>
            </div>
            
            <div class="flex justify-center gap-6 mb-6">
                <a href="#" class="text-gray-300 hover:text-white transition-colors">
                    <i class="fab fa-facebook-f text-xl"></i>
                </a>
                <a href="#" class="text-gray-300 hover:text-white transition-colors">
                    <i class="fab fa-instagram text-xl"></i>
                </a>
                <a href="#" class="text-gray-300 hover:text-white transition-colors">
                    <i class="fab fa-telegram text-xl"></i>
                </a>
            </div>
            
            <p style="color: var(--gray-400);">
                © <?php echo date('Y'); ?> Ghidul Tinerilor Chișinău. Toate drepturile rezervate.
            </p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>

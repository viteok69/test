<?php
require_once 'config/config.php';

// Get place ID
$place_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($place_id <= 0) {
    redirect('places.php');
}

$database = new Database();
$db = $database->getConnection();

// Get place details
$query = "SELECT p.*, c.name_ro as category_name, c.color as category_color, c.icon as category_icon,
          u.username as submitted_by_username
          FROM places p 
          JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.submitted_by = u.id
          WHERE p.id = :place_id AND p.is_active = 1";

$stmt = $db->prepare($query);
$stmt->bindParam(':place_id', $place_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    redirect('places.php');
}

$place = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user has favorited this place
$is_favorite = false;
if (isLoggedIn()) {
    $fav_query = "SELECT id FROM user_favorites WHERE user_id = :user_id AND place_id = :place_id";
    $fav_stmt = $db->prepare($fav_query);
    $fav_stmt->bindParam(':user_id', $_SESSION['user_id']);
    $fav_stmt->bindParam(':place_id', $place_id);
    $fav_stmt->execute();
    $is_favorite = $fav_stmt->rowCount() > 0;
}

// Get similar places (same category, excluding current)
$similar_query = "SELECT p.*, c.name_ro as category_name, c.color as category_color
                  FROM places p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.category_id = :category_id 
                  AND p.id != :place_id 
                  AND p.is_active = 1 
                  ORDER BY p.rating DESC, p.created_at DESC 
                  LIMIT 3";

$similar_stmt = $db->prepare($similar_query);
$similar_stmt->bindParam(':category_id', $place['category_id']);
$similar_stmt->bindParam(':place_id', $place_id);
$similar_stmt->execute();
$similar_places = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total favorites count
$fav_count_query = "SELECT COUNT(*) as count FROM user_favorites WHERE place_id = :place_id";
$fav_count_stmt = $db->prepare($fav_count_query);
$fav_count_stmt->bindParam(':place_id', $place_id);
$fav_count_stmt->execute();
$favorites_count = $fav_count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($place['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($place['description'], 0, 160)); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($place['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr($place['description'], 0, 160)); ?>">
    <meta property="og:type" content="place">
    <meta property="og:url" content="<?php echo SITE_URL; ?>/place.php?id=<?php echo $place_id; ?>">
    <?php if ($place['image']): ?>
        <meta property="og:image" content="<?php echo SITE_URL; ?>/<?php echo $place['image']; ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Leaflet CSS for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-map-marked-alt"></i>
                Ghidul Tinerilor
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Acasă</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categorii</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="places.php">Locuri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="discussions.php">Discuții</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="theme-toggle me-3" onclick="toggleTheme()" title="Schimbă tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-user"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">Ieșire</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Conectare</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="auth/register.php">Înregistrare</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <section class="py-3 mt-5 bg-light">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-home"></i> Acasă
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="places.php" class="text-decoration-none">Locuri</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="places.php?category=<?php echo $place['category_id']; ?>" class="text-decoration-none">
                            <?php echo $place['category_name']; ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($place['title']); ?>
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Place Header -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Place Image -->
                <div class="col-lg-8">
                    <div class="place-image-container position-relative mb-4">
                        <?php if ($place['image']): ?>
                            <img src="<?php echo $place['image']; ?>" 
                                 class="img-fluid rounded-custom w-100" 
                                 alt="<?php echo htmlspecialchars($place['title']); ?>"
                                 style="height: 400px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-gradient d-flex align-items-center justify-content-center rounded-custom" 
                                 style="height: 400px; background: <?php echo $place['category_color']; ?>;">
                                <i class="<?php echo $place['category_icon']; ?> text-white" style="font-size: 5rem; opacity: 0.7;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Category Badge -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge fs-6 px-3 py-2" style="background-color: <?php echo $place['category_color']; ?>;">
                                <i class="<?php echo $place['category_icon']; ?> me-2"></i>
                                <?php echo $place['category_name']; ?>
                            </span>
                        </div>
                        
                        <!-- Favorite Button -->
                        <?php if (isLoggedIn()): ?>
                            <button class="favorite-btn position-absolute top-0 end-0 m-3 <?php echo $is_favorite ? 'active' : ''; ?>" 
                                    onclick="toggleFavorite(<?php echo $place_id; ?>)"
                                    data-place-id="<?php echo $place_id; ?>">
                                <i class="fas fa-heart fa-lg"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Place Info -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h1 class="card-title h2 mb-3"><?php echo htmlspecialchars($place['title']); ?></h1>
                            
                            <!-- Rating -->
                            <?php if ($place['rating'] > 0): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="text-warning me-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $place['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="fw-bold me-2"><?php echo number_format($place['rating'], 1); ?></span>
                                    <small class="text-muted">(<?php echo $favorites_count; ?> favorite)</small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Contact Info -->
                            <div class="contact-info">
                                <?php if ($place['address']): ?>
                                    <div class="d-flex align-items-start mb-3">
                                        <i class="fas fa-map-marker-alt text-primary me-3 mt-1"></i>
                                        <div>
                                            <strong>Adresă:</strong><br>
                                            <span class="text-muted"><?php echo htmlspecialchars($place['address']); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($place['phone']): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-phone text-success me-3"></i>
                                        <div>
                                            <strong>Telefon:</strong><br>
                                            <a href="tel:<?php echo $place['phone']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($place['phone']); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($place['website']): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-globe text-info me-3"></i>
                                        <div>
                                            <strong>Website:</strong><br>
                                            <a href="<?php echo $place['website']; ?>" 
                                               target="_blank" 
                                               class="text-decoration-none">
                                                Vizitează site-ul <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-grid gap-2 mt-4">
                                <?php if ($place['phone']): ?>
                                    <a href="tel:<?php echo $place['phone']; ?>" class="btn btn-success">
                                        <i class="fas fa-phone"></i> Sună Acum
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($place['website']): ?>
                                    <a href="<?php echo $place['website']; ?>" 
                                       target="_blank" 
                                       class="btn btn-primary">
                                        <i class="fas fa-external-link-alt"></i> Vizitează Website
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($place['latitude'] && $place['longitude']): ?>
                                    <button class="btn btn-outline-primary" onclick="showDirections()">
                                        <i class="fas fa-directions"></i> Cum Ajung Aici
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-secondary" onclick="sharePlace()">
                                    <i class="fas fa-share-alt"></i> Distribuie
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Place Details -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Description -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Despre acest loc
                            </h3>
                            <p class="card-text lead"><?php echo nl2br(htmlspecialchars($place['description'])); ?></p>
                            
                            <?php if ($place['submitted_by_username']): ?>
                                <hr>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    Sugerat de: <strong><?php echo htmlspecialchars($place['submitted_by_username']); ?></strong>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d.m.Y', strtotime($place['created_at'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Map -->
                    <?php if ($place['latitude'] && $place['longitude']): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h3 class="card-title">
                                    <i class="fas fa-map text-primary me-2"></i>
                                    Localizare
                                </h3>
                                <div id="map" style="height: 300px; border-radius: 8px;"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Comments Section (Placeholder) -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">
                                <i class="fas fa-comments text-primary me-2"></i>
                                Comentarii și Recenzii
                            </h3>
                            
                            <?php if (isLoggedIn()): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Funcționalitatea de comentarii va fi disponibilă în curând!
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <a href="auth/login.php" class="alert-link">Conectează-te</a> pentru a lăsa un comentariu.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title">Statistici</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="stats-item">
                                        <div class="stats-number text-danger"><?php echo $favorites_count; ?></div>
                                        <div class="stats-label">Favorite</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stats-item">
                                        <div class="stats-number text-primary">
                                            <?php echo $place['rating'] > 0 ? number_format($place['rating'], 1) : 'N/A'; ?>
                                        </div>
                                        <div class="stats-label">Rating</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Similar Places -->
                    <?php if (!empty($similar_places)): ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-thumbs-up text-primary me-2"></i>
                                    Locuri Similare
                                </h5>
                                
                                <?php foreach ($similar_places as $similar): ?>
                                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                        <div class="flex-shrink-0 me-3">
                                            <?php if ($similar['image']): ?>
                                                <img src="<?php echo $similar['image']; ?>" 
                                                     class="rounded" 
                                                     width="60" 
                                                     height="60" 
                                                     style="object-fit: cover;"
                                                     alt="<?php echo htmlspecialchars($similar['title']); ?>">
                                            <?php else: ?>
                                                <div class="bg-gradient rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px; background: <?php echo $similar['category_color']; ?>;">
                                                    <i class="fas fa-image text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="place.php?id=<?php echo $similar['id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars($similar['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo substr(htmlspecialchars($similar['description']), 0, 60) . '...'; ?>
                                            </small>
                                            <?php if ($similar['rating'] > 0): ?>
                                                <div class="mt-1">
                                                    <small class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $similar['rating'] ? '' : '-o'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <a href="places.php?category=<?php echo $place['category_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-th-large"></i> Vezi Toate din <?php echo $place['category_name']; ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="brand-title text-white">
                        <i class="fas fa-map-marked-alt"></i>
                        Ghidul Tinerilor Chișinău
                    </h5>
                    <p class="text-muted">Platforma ta pentru a descoperi și explora cele mai bune oportunități din Chișinău.</p>
                </div>
                
                <div class="col-md-3">
                    <h6>Link-uri Utile</h6>
                    <ul class="list-unstyled">
                        <li><a href="categories.php" class="text-muted">Categorii</a></li>
                        <li><a href="places.php" class="text-muted">Locuri</a></li>
                        <li><a href="discussions.php" class="text-muted">Discuții</a></li>
                        <li><a href="contact.php" class="text-muted">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6>Suport</h6>
                    <ul class="list-unstyled">
                        <li><a href="help.php" class="text-muted">Ajutor</a></li>
                        <li><a href="privacy.php" class="text-muted">Confidențialitate</a></li>
                        <li><a href="terms.php" class="text-muted">Termeni</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 Ghidul Tinerilor Chișinău. Toate drepturile rezervate.</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize map if coordinates are available
        <?php if ($place['latitude'] && $place['longitude']): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('map').setView([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            const marker = L.marker([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>])
                .addTo(map)
                .bindPopup('<strong><?php echo addslashes(htmlspecialchars($place['title'])); ?></strong><br><?php echo addslashes(htmlspecialchars($place['address'])); ?>')
                .openPopup();
        });
        <?php endif; ?>
        
        // Share functionality
        function sharePlace() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes(htmlspecialchars($place['title'])); ?>',
                    text: '<?php echo addslashes(htmlspecialchars(substr($place['description'], 0, 100))); ?>',
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showToast('Link-ul a fost copiat în clipboard!', 'success');
                }).catch(() => {
                    showToast('Nu s-a putut copia link-ul', 'error');
                });
            }
        }
        
        // Directions functionality
        function showDirections() {
            <?php if ($place['latitude'] && $place['longitude']): ?>
            const lat = <?php echo $place['latitude']; ?>;
            const lng = <?php echo $place['longitude']; ?>;
            const address = '<?php echo addslashes(htmlspecialchars($place['address'])); ?>';
            
            // Try to get user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        const url = `https://www.google.com/maps/dir/${userLat},${userLng}/${lat},${lng}`;
                        window.open(url, '_blank');
                    },
                    function() {
                        // Fallback - just show destination
                        const url = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
                        window.open(url, '_blank');
                    }
                );
            } else {
                const url = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
                window.open(url, '_blank');
            }
            <?php endif; ?>
        }
    </script>
</body>
</html>

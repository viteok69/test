<?php
require_once 'config/config.php';
require_once 'services/FreePlacesService.php';

$place_id = isset($_GET['id']) ? sanitizeInput($_GET['id']) : '';

if (empty($place_id)) {
    header('Location: places.php');
    exit;
}

// For this demo, we'll create a simple detail view
// In a real app, you'd store the place data or fetch it again
$placesService = new FreePlacesService();

// Try to get place from different sources based on ID prefix
$place = null;
if (strpos($place_id, 'curated_') === 0) {
    $curated_places = $placesService->getRecommendedPlaces(20);
    foreach ($curated_places as $p) {
        if ($p['id'] === $place_id) {
            $place = $p;
            break;
        }
    }
}

if (!$place) {
    // If not found, redirect back
    header('Location: places.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($place['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($place['description']); ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
            
            <div class="navbar-nav ms-auto">
                <a href="places.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Înapoi la Locuri
                </a>
            </div>
        </div>
    </nav>

    <!-- Place Detail -->
    <section class="py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Place Header -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h1 class="card-title"><?php echo htmlspecialchars($place['title']); ?></h1>
                                <span class="badge bg-primary fs-6"><?php echo $place['category']; ?></span>
                            </div>
                            
                            <p class="card-text"><?php echo htmlspecialchars($place['description']); ?></p>
                            
                            <div class="row g-3">
                                <?php if ($place['address']): ?>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-map-marker-alt text-primary"></i> Adresa:</strong><br>
                                    <?php echo htmlspecialchars($place['address']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($place['phone']): ?>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-phone text-success"></i> Telefon:</strong><br>
                                    <a href="tel:<?php echo $place['phone']; ?>"><?php echo $place['phone']; ?></a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($place['website']): ?>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-globe text-info"></i> Website:</strong><br>
                                    <a href="<?php echo $place['website']; ?>" target="_blank">Vizitează site-ul</a>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-6">
                                    <strong><i class="fas fa-database text-secondary"></i> Sursă:</strong><br>
                                    <?php echo ucfirst($place['source']); ?> (Gratuit)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Map -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-map"></i> Locația pe hartă</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="map" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Acțiuni</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if ($place['phone']): ?>
                                <a href="tel:<?php echo $place['phone']; ?>" class="btn btn-success">
                                    <i class="fas fa-phone"></i> Sună
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($place['website']): ?>
                                <a href="<?php echo $place['website']; ?>" target="_blank" class="btn btn-info">
                                    <i class="fas fa-external-link-alt"></i> Vizitează Site-ul
                                </a>
                                <?php endif; ?>
                                
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $place['latitude']; ?>,<?php echo $place['longitude']; ?>" 
                                   target="_blank" class="btn btn-primary">
                                    <i class="fas fa-directions"></i> Direcții
                                </a>
                                
                                <?php if (isLoggedIn()): ?>
                                <button class="btn btn-outline-danger" onclick="toggleFavorite('<?php echo $place['id']; ?>')">
                                    <i class="fas fa-heart"></i> Adaugă la Favorite
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Informații</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Importanță:</strong>
                                <div class="text-warning">
                                    <?php 
                                    $importance = $place['importance'] ?? 0;
                                    $stars = round($importance * 5);
                                    for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $stars ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted"><?php echo number_format($importance, 2); ?>/1.0</small>
                            </div>
                            
                            <?php if (isset($place['opening_hours']) && $place['opening_hours']): ?>
                            <div class="mb-3">
                                <strong>Program:</strong><br>
                                <small><?php echo htmlspecialchars($place['opening_hours']); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Coordonate:</strong><br>
                                <small class="text-muted">
                                    <?php echo number_format($place['latitude'], 6); ?>, 
                                    <?php echo number_format($place['longitude'], 6); ?>
                                </small>
                            </div>
                            
                            <div class="alert alert-success">
                                <small>
                                    <i class="fas fa-check-circle"></i>
                                    Date gratuite din <?php echo ucfirst($place['source']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Initialize map
        const map = L.map('map').setView([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>], 16);
        
        // Use free OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add marker
        L.marker([<?php echo $place['latitude']; ?>, <?php echo $place['longitude']; ?>]).addTo(map)
            .bindPopup('<?php echo htmlspecialchars($place['title']); ?>')
            .openPopup();
    </script>
</body>
</html>

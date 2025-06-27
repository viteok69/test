<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user's favorite places
$query = "SELECT p.*, c.name_ro as category_name, c.color as category_color
          FROM places p 
          JOIN categories c ON p.category_id = c.id 
          JOIN user_favorites uf ON p.id = uf.place_id
          WHERE uf.user_id = :user_id 
          ORDER BY uf.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$favorite_places = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritele Mele - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-user"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/logout.php">Ieșire</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="py-5 mt-5 bg-gradient text-white">
        <div class="container">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="fas fa-heart me-3"></i>
                    Favoritele Mele
                </h1>
                <p class="lead">Locurile tale preferate din Chișinău</p>
            </div>
        </div>
    </section>

    <!-- Favorites Grid -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($favorite_places)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-heart fa-5x text-muted mb-4"></i>
                    <h2 class="mb-3">Nu ai încă locuri favorite</h2>
                    <p class="lead text-muted mb-4">Explorează locurile din Chișinău și salvează-le ca favorite!</p>
                    <a href="places.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-compass"></i> Explorează Locuri
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($favorite_places as $place): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card place-card h-100 fade-in">
                                <?php if ($place['image']): ?>
                                    <img src="<?php echo $place['image']; ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($place['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center" 
                                         style="height: 200px; background: <?php echo $place['category_color']; ?>;">
                                        <i class="fas fa-image text-white" style="font-size: 3rem; opacity: 0.5;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <button class="favorite-btn active" 
                                        onclick="toggleFavorite(<?php echo $place['id']; ?>)"
                                        data-place-id="<?php echo $place['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="place.php?id=<?php echo $place['id']; ?>" 
                                               class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($place['title']); ?>
                                            </a>
                                        </h5>
                                        <span class="badge ms-2" 
                                              style="background-color: <?php echo $place['category_color']; ?>">
                                            <?php echo $place['category_name']; ?>
                                        </span>
                                    </div>
                                    
                                    <p class="card-text text-muted">
                                        <?php echo substr(htmlspecialchars($place['description']), 0, 120) . '...'; ?>
                                    </p>
                                    
                                    <?php if ($place['address']): ?>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                <?php echo htmlspecialchars($place['address']); ?>
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($place['rating'] > 0): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="text-warning me-2">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $place['rating'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?php echo number_format($place['rating'], 1); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-2">
                                        <a href="place.php?id=<?php echo $place['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill">
                                            <i class="fas fa-info-circle"></i> Detalii
                                        </a>
                                        
                                        <?php if ($place['website']): ?>
                                            <a href="<?php echo $place['website']; ?>" 
                                               target="_blank" 
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($place['phone']): ?>
                                            <a href="tel:<?php echo $place['phone']; ?>" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-5">
                    <a href="places.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Caută Mai Multe Locuri
                    </a>
                </div>
            <?php endif; ?>
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
    <script src="assets/js/main.js"></script>
</body>
</html>

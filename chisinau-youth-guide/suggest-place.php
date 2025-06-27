<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get categories
$query = "SELECT * FROM categories ORDER BY name_ro";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    $website = sanitizeInput($_POST['website']);
    
    // Validation
    if (empty($title) || empty($description) || $category_id <= 0) {
        $error_message = 'Titlul, descrierea și categoria sunt obligatorii.';
    } elseif (strlen($title) < 3) {
        $error_message = 'Titlul trebuie să aibă cel puțin 3 caractere.';
    } elseif (strlen($description) < 20) {
        $error_message = 'Descrierea trebuie să aibă cel puțin 20 de caractere.';
    } else {
        // Validate website URL if provided
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $error_message = 'Website-ul nu este valid.';
        } else {
            // Insert suggestion
            $query = "INSERT INTO suggestions (user_id, title, description, category_id, address, status) 
                     VALUES (:user_id, :title, :description, :category_id, :address, 'pending')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':address', $address);
            
            if ($stmt->execute()) {
                $success_message = 'Sugestia ta a fost trimisă cu succes! Va fi revizuită de echipa noastră.';
                // Clear form
                $_POST = [];
            } else {
                $error_message = 'A apărut o eroare. Te rugăm să încerci din nou.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugerează un Loc - <?php echo SITE_NAME; ?></title>
    
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
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Sugerează un Loc Nou</h1>
                    <p class="lead">Ajută comunitatea să crească prin sugerarea de locuri interesante pentru tinerii din Chișinău!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body p-4">
                            <form method="POST" id="suggest-form">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-tag text-primary me-2"></i>
                                            Numele locului *
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               placeholder="ex: Cafeneaua Centrală, Biblioteca Tehnică..." 
                                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                               required>
                                        <div class="form-text">Minimum 3 caractere</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="category_id" class="form-label">
                                            <i class="fas fa-list text-primary me-2"></i>
                                            Categoria *
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Alege categoria</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $category['name_ro']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left text-primary me-2"></i>
                                        Descrierea locului *
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              placeholder="Descrie locul în detaliu: ce oferă, de ce este special, ce activități se pot face acolo..." 
                                              required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    <div class="form-text">Minimum 20 de caractere. Fii cât mai descriptiv!</div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        Adresa
                                    </label>
                                    <input type="text" class="form-control" id="address" name="address" 
                                           placeholder="ex: Str. Ștefan cel Mare 64, Chișinău" 
                                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                    <div class="form-text">Adresa exactă ajută alți utilizatori să găsească locul</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            Numărul de telefon
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="ex: +373 22 123456" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="website" class="form-label">
                                            <i class="fas fa-globe text-primary me-2"></i>
                                            Website-ul
                                        </label>
                                        <input type="url" class="form-control" id="website" name="website" 
                                               placeholder="ex: https://example.com" 
                                               value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Notă:</strong> Sugestia ta va fi revizuită de echipa noastră înainte de a fi publicată. 
                                    Vei fi notificat prin email despre statusul sugestiei tale.
                                </div>

                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-paper-plane"></i> Trimite Sugestia
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-arrow-left"></i> Înapoi la Dashboard
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar with Tips -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                Sfaturi pentru o sugestie bună
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Fii specific:</strong> Include detalii despre ce face locul special
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Adaugă context:</strong> Explică de ce ar fi util pentru tineri
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Verifică informațiile:</strong> Asigură-te că datele de contact sunt corecte
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Alege categoria potrivită:</strong> Ajută utilizatorii să găsească locul mai ușor
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-question-circle text-info me-2"></i>
                                Ai nevoie de ajutor?
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Dacă ai întrebări despre cum să sugerezi un loc, nu ezita să ne contactezi!</p>
                            <a href="contact.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-envelope"></i> Contactează-ne
                            </a>
                        </div>
                    </div>
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
    <script src="assets/js/main.js"></script>
    
    <script>
        // Form validation
        document.getElementById('suggest-form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const category = document.getElementById('category_id').value;
            
            if (title.length < 3) {
                e.preventDefault();
                showToast('Titlul trebuie să aibă cel puțin 3 caractere.', 'error');
                return;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                showToast('Descrierea trebuie să aibă cel puțin 20 de caractere.', 'error');
                return;
            }
            
            if (!category) {
                e.preventDefault();
                showToast('Te rugăm să alegi o categorie.', 'error');
                return;
            }
        });

        // Character counter for description
        const descriptionField = document.getElementById('description');
        const descriptionHelp = descriptionField.nextElementSibling;
        
        descriptionField.addEventListener('input', function() {
            const length = this.value.length;
            const remaining = Math.max(0, 20 - length);
            
            if (remaining > 0) {
                descriptionHelp.textContent = `Încă ${remaining} caractere necesare. Minimum 20 de caractere.`;
                descriptionHelp.className = 'form-text text-warning';
            } else {
                descriptionHelp.textContent = `${length} caractere. Fii cât mai descriptiv!`;
                descriptionHelp.className = 'form-text text-success';
            }
        });
    </script>
</body>
</html>

<?php
session_start();
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $name = sanitizeInput($_POST['name']);
        $category = sanitizeInput($_POST['category']);
        $address = sanitizeInput($_POST['address']);
        $description = sanitizeInput($_POST['description']);
        $website_url = sanitizeUrl($_POST['website_url']);
        $image_url = sanitizeUrl($_POST['image_url']);
        $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        $phone = sanitizeInput($_POST['phone']);
        $opening_hours = sanitizeInput($_POST['opening_hours']);
        $price_range = sanitizeInput($_POST['price_range']);
        $rating = !empty($_POST['rating']) ? floatval($_POST['rating']) : 0.0;

        // Validate required fields
        if (empty($name) || empty($category) || empty($address) || empty($description)) {
            throw new Exception('Toate câmpurile obligatorii trebuie completate.');
        }

        // Validate category
        $valid_categories = ['park', 'restaurant', 'cafe', 'museum', 'shopping', 'education', 'entertainment', 'sports', 'coworking', 'nightlife', 'health', 'transport'];
        if (!in_array($category, $valid_categories)) {
            throw new Exception('Categoria selectată nu este validă.');
        }

        // Validate price range
        $valid_prices = ['free', 'budget', 'moderate', 'expensive'];
        if (!in_array($price_range, $valid_prices)) {
            throw new Exception('Gama de prețuri selectată nu este validă.');
        }

        // Validate rating
        if ($rating < 0 || $rating > 5) {
            throw new Exception('Rating-ul trebuie să fie între 0 și 5.');
        }

        // Validate URLs if provided
        if ($website_url === false) {
            throw new Exception('URL-ul website-ului nu este valid. Încearcă: example.com sau https://example.com');
        }

        if ($image_url === false) {
            throw new Exception('URL-ul imaginii nu este valid. Încearcă: example.com/image.jpg sau https://example.com/image.jpg');
        }

        // Insert into database using prepared statement
        $query = "INSERT INTO places (name, category, address, description, website_url, image_url, latitude, longitude, phone, opening_hours, price_range, rating, added_by_user, created_at) 
                  VALUES (:name, :category, :address, :description, :website_url, :image_url, :latitude, :longitude, :phone, :opening_hours, :price_range, :rating, 1, NOW())";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':address' => $address,
            ':description' => $description,
            ':website_url' => !empty($website_url) ? $website_url : null,
            ':image_url' => !empty($image_url) ? $image_url : null,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':phone' => !empty($phone) ? $phone : null,
            ':opening_hours' => !empty($opening_hours) ? $opening_hours : null,
            ':price_range' => $price_range,
            ':rating' => $rating
        ]);

        if ($result) {
            $message = "Locul '{$name}' a fost adăugat cu succes în baza de date și este acum disponibil pentru căutare!";
            $message_type = 'success';
            
            // Clear form data after successful submission
            $_POST = [];
        } else {
            throw new Exception('Eroare la salvarea în baza de date.');
        }
        
    } catch (Exception $e) {
        $message = 'Eroare la adăugarea locului: ' . $e->getMessage();
        $message_type = 'error';
        error_log("Admin form error: " . $e->getMessage());
    }
}

// Get recent places
try {
    $recent_query = "SELECT * FROM places ORDER BY created_at DESC LIMIT 10";
    $recent_stmt = $db->prepare($recent_query);
    $recent_stmt->execute();
    $recent_places = $recent_stmt->fetchAll();
} catch (Exception $e) {
    $recent_places = [];
    error_log("Error fetching recent places: " . $e->getMessage());
}

// Category options
$categories = [
    'park' => '🌳 Parc',
    'restaurant' => '🍽️ Restaurant',
    'cafe' => '☕ Cafenea',
    'museum' => '🏛️ Muzeu',
    'shopping' => '🛍️ Shopping',
    'education' => '🎓 Educație',
    'entertainment' => '🎬 Divertisment',
    'sports' => '⚽ Sport',
    'coworking' => '💻 Coworking',
    'nightlife' => '🌙 Viața de noapte',
    'health' => '🏥 Sănătate',
    'transport' => '🚌 Transport'
];

$price_ranges = [
    'free' => 'Gratuit',
    'budget' => 'Buget mic',
    'moderate' => 'Moderat',
    'expensive' => 'Scump'
];
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Chișinău Guide</title>
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
                    <li><a href="index.php" class="nav-link">Acasă</a></li>
                    <li><a href="admin.php" class="nav-link active">Admin</a></li>
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

    <div class="container py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-cog text-primary"></i>
                Panou de Administrare
            </h1>
            <p class="text-xl text-gray-600">Adaugă locuri noi în ghidul Chișinău</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> mb-6">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-3 gap-8">
            <!-- Add Place Form -->
            <div class="col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-2xl font-bold">
                            <i class="fas fa-plus-circle text-primary"></i>
                            Adaugă Loc Nou
                        </h2>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" class="space-y-6" id="admin-form">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="name" class="form-label">Nume *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           placeholder="ex: Torro Burgers" required
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="category" class="form-label">Categorie *</label>
                                    <select id="category" name="category" class="form-select" required>
                                        <option value="">Alege categoria</option>
                                        <?php foreach ($categories as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" 
                                                    <?php echo ($_POST['category'] ?? '') === $value ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Adresa *</label>
                                <input type="text" id="address" name="address" class="form-input" 
                                       placeholder="ex: Strada Columna 45, Chișinău" required
                                       value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">Descriere *</label>
                                <textarea id="description" name="description" class="form-input" rows="4" 
                                          placeholder="Descrie locul în detaliu..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="website_url" class="form-label">Website URL</label>
                                <input type="text" id="website_url" name="website_url" class="form-input" 
                                       placeholder="example.com sau https://example.com"
                                       value="<?php echo htmlspecialchars($_POST['website_url'] ?? ''); ?>">
                                <small class="text-gray-500">Poți introduce cu sau fără https:// (ex: torro.md sau https://torro.md)</small>
                            </div>

                            <div class="form-group">
                                <label for="image_url" class="form-label">URL Imagine</label>
                                <input type="url" id="image_url" name="image_url" class="form-input" 
                                       placeholder="https://example.com/image.jpg"
                                       value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>">
                                <small class="text-gray-500">URL-ul unei imagini reprezentative (opțional)</small>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="latitude" class="form-label">Latitudine</label>
                                    <input type="number" id="latitude" name="latitude" class="form-input" 
                                           step="0.000001" placeholder="47.0245"
                                           value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="longitude" class="form-label">Longitudine</label>
                                    <input type="number" id="longitude" name="longitude" class="form-input" 
                                           step="0.000001" placeholder="28.8322"
                                           value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="tel" id="phone" name="phone" class="form-input" 
                                           placeholder="+373 22 123-456"
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="opening_hours" class="form-label">Program</label>
                                    <input type="text" id="opening_hours" name="opening_hours" class="form-input" 
                                           placeholder="09:00-18:00"
                                           value="<?php echo htmlspecialchars($_POST['opening_hours'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label for="price_range" class="form-label">Preț</label>
                                    <select id="price_range" name="price_range" class="form-select">
                                        <?php foreach ($price_ranges as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" 
                                                    <?php echo ($_POST['price_range'] ?? 'moderate') === $value ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="rating" class="form-label">Rating (0-5)</label>
                                    <input type="number" id="rating" name="rating" class="form-input" 
                                           min="0" max="5" step="0.1" placeholder="4.5"
                                           value="<?php echo htmlspecialchars($_POST['rating'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                    <i class="fas fa-plus"></i>
                                    Adaugă Locul
                                </button>
                                
                                <button type="reset" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-undo"></i>
                                    Resetează
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Places Sidebar -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-xl font-bold">
                            <i class="fas fa-clock text-accent"></i>
                            Locuri Recente
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($recent_places)): ?>
                            <p class="text-gray-500 text-center py-4">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                Nu există locuri încă
                            </p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_places as $place): ?>
                                    <div class="border-l-4 border-primary pl-4 py-2">
                                        <h4 class="font-semibold text-gray-900 mb-1">
                                            <?php echo htmlspecialchars($place['name']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-600 mb-1">
                                            <?php echo $categories[$place['category']] ?? ucfirst($place['category']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?php echo date('d.m.Y H:i', strtotime($place['created_at'])); ?>
                                        </p>
                                        <?php if ($place['added_by_user']): ?>
                                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mt-1">
                                                Adăugat de utilizator
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer">
                        <a href="index.php" class="btn btn-outline w-full">
                            <i class="fas fa-eye"></i>
                            Vezi toate locurile
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-6">
                    <div class="card-header">
                        <h3 class="text-xl font-bold">
                            <i class="fas fa-chart-bar text-coral"></i>
                            Statistici
                        </h3>
                    </div>
                    
                    <div class="card-body">
                        <?php
                        try {
                            $stats_query = "SELECT 
                                COUNT(*) as total,
                                COUNT(CASE WHEN added_by_user = 1 THEN 1 END) as user_added,
                                COUNT(DISTINCT category) as categories
                                FROM places";
                            $stats_stmt = $db->prepare($stats_query);
                            $stats_stmt->execute();
                            $stats = $stats_stmt->fetch();
                        } catch (Exception $e) {
                            $stats = ['total' => 0, 'user_added' => 0, 'categories' => 0];
                            error_log("Error fetching stats: " . $e->getMessage());
                        }
                        ?>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total locuri:</span>
                                <span class="font-bold text-2xl text-primary"><?php echo $stats['total']; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Adăugate de utilizatori:</span>
                                <span class="font-bold text-xl text-accent"><?php echo $stats['user_added']; ?></span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Categorii active:</span>
                                <span class="font-bold text-xl text-coral"><?php echo $stats['categories']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

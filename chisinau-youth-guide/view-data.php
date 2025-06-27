<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Get all places with optional filtering
$category_filter = $_GET['category'] ?? '';
$search_filter = $_GET['search'] ?? '';

$query = "SELECT * FROM places WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $query .= " AND category = :category";
    $params[':category'] = $category_filter;
}

if (!empty($search_filter)) {
    $query .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = '%' . $search_filter . '%';
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $places = $stmt->fetchAll();
} catch (Exception $e) {
    $places = [];
    $error = "Error fetching data: " . $e->getMessage();
}

// Get statistics
try {
    $stats_query = "SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN added_by_user = 1 THEN 1 END) as user_added,
        COUNT(DISTINCT category) as categories,
        AVG(rating) as avg_rating
        FROM places";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();
} catch (Exception $e) {
    $stats = ['total' => 0, 'user_added' => 0, 'categories' => 0, 'avg_rating' => 0];
}

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
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer - Chișinău Guide</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-user {
            background-color: #10b981;
            color: white;
        }
        .badge-system {
            background-color: #6b7280;
            color: white;
        }
        .url-link {
            color: #3b82f6;
            text-decoration: none;
        }
        .url-link:hover {
            text-decoration: underline;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .filters {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
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
                
                <ul class="navbar-nav">
                    <li><a href="index.php" class="nav-link">Acasă</a></li>
                    <li><a href="admin.php" class="nav-link">Admin</a></li>
                    <li><a href="view-data.php" class="nav-link active">Database</a></li>
                </ul>

                <button class="theme-toggle" onclick="toggleTheme()" title="Schimbă tema">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-database text-primary"></i>
                Database Viewer
            </h1>
            <p class="text-xl text-gray-600">Vezi toate datele din baza de date</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="text-gray-600">Total Locuri</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['user_added']; ?></div>
                <div class="text-gray-600">Adăugate de Utilizatori</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['categories']; ?></div>
                <div class="text-gray-600">Categorii Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                <div class="text-gray-600">Rating Mediu</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="flex gap-4 items-end">
                <div class="form-group">
                    <label for="search" class="form-label">Caută:</label>
                    <input type="text" id="search" name="search" class="form-input" 
                           placeholder="Nume sau descriere..." 
                           value="<?php echo htmlspecialchars($search_filter); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category" class="form-label">Categorie:</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">Toate categoriile</option>
                        <?php foreach ($categories as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo $category_filter === $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Filtrează
                </button>
                
                <a href="view-data.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Resetează
                </a>
            </form>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-table text-primary"></i>
                    Date din Baza de Date (<?php echo count($places); ?> rezultate)
                </h2>
            </div>
            
            <div class="card-body" style="overflow-x: auto;">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php elseif (empty($places)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                        <p class="text-xl text-gray-600">Nu s-au găsit rezultate</p>
                        <a href="admin.php" class="btn btn-primary mt-4">
                            <i class="fas fa-plus"></i>
                            Adaugă primul loc
                        </a>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nume</th>
                                <th>Categorie</th>
                                <th>Adresa</th>
                                <th>Descriere</th>
                                <th>Website</th>
                                <th>Rating</th>
                                <th>Preț</th>
                                <th>Sursă</th>
                                <th>Data Creării</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($places as $place): ?>
                                <tr>
                                    <td><?php echo $place['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($place['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo $categories[$place['category']] ?? ucfirst($place['category']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($place['address']); ?></td>
                                    <td>
                                        <?php 
                                        $desc = htmlspecialchars($place['description']);
                                        echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($place['website_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($place['website_url']); ?>" 
                                               target="_blank" class="url-link">
                                                <i class="fas fa-external-link-alt"></i>
                                                Visit
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($place['rating'] > 0): ?>
                                            <span class="text-yellow-500">
                                                <?php echo $place['rating']; ?> ⭐
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="capitalize"><?php echo $place['price_range']; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($place['added_by_user']): ?>
                                            <span class="badge badge-user">Utilizator</span>
                                        <?php else: ?>
                                            <span class="badge badge-system">Sistem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d.m.Y H:i', strtotime($place['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex gap-4 justify-center mt-8">
            <a href="admin.php" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Adaugă Loc Nou
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Înapoi la Site
            </a>
            <button onclick="window.print()" class="btn btn-outline">
                <i class="fas fa-print"></i>
                Printează
            </button>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

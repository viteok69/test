<?php
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    flashMessage('error', 'Trebuie să te conectezi pentru a accesa această pagină.');
    redirect('auth/login.php');
}

$user = getCurrentUser();
if (!$user) {
    redirect('auth/login.php');
}

$success = getFlashMessage('success');
$error = getFlashMessage('error');
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Panoul tău personal pentru ghidul tinerilor din Chișinău.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-map-marked-alt"></i>
                Ghidul Tinerilor
            </a>
            
            <button class="navbar-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="navbar-nav" id="navbar-nav">
                <li><a href="index.php" class="nav-link">Acasă</a></li>
                <li><a href="places.php" class="nav-link">Locuri</a></li>
                <li><a href="events.php" class="nav-link">Evenimente</a></li>
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li>
                    <button class="theme-toggle" onclick="toggleTheme()" title="Schimbă tema">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                </li>
                <li><a href="auth/logout.php" class="btn btn-secondary btn-sm">Ieșire</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="flex items-center justify-center mb-6">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                    <i class="fas fa-user text-primary text-2xl"></i>
                </div>
            </div>
            <h1>Bună, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p>Bun venit în panoul tău personal. Aici poți gestiona profilul și explora Chișinăul.</p>
            <div class="btn-group">
                <a href="places.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-map"></i>
                    Explorează Locuri
                </a>
                <a href="events.php" class="btn btn-accent btn-lg">
                    <i class="fas fa-calendar"></i>
                    Vezi Evenimente
                </a>
            </div>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="py-16">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-3 gap-6">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="flex items-center gap-2">
                            <i class="fas fa-user text-primary"></i>
                            Profilul Meu
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Nume complet</label>
                                <p class="font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Email</label>
                                <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Nume utilizator</label>
                                <p class="font-medium">@<?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                            <?php if ($user['age']): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Vârsta</label>
                                <p class="font-medium"><?php echo $user['age']; ?> ani</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($user['city']): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Oraș</label>
                                <p class="font-medium"><?php echo htmlspecialchars($user['city']); ?></p>
                            </div>
                            <?php endif; ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Membru din</label>
                                <p class="font-medium"><?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="profile/edit.php" class="btn btn-primary w-full">
                            <i class="fas fa-edit"></i>
                            Editează Profilul
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="flex items-center gap-2">
                            <i class="fas fa-bolt text-accent"></i>
                            Acțiuni Rapide
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <a href="places.php" class="btn btn-secondary w-full">
                                <i class="fas fa-map-marker-alt"></i>
                                Explorează Locuri
                            </a>
                            <a href="events.php" class="btn btn-secondary w-full">
                                <i class="fas fa-calendar-alt"></i>
                                Vezi Evenimente
                            </a>
                            <a href="favorites.php" class="btn btn-secondary w-full">
                                <i class="fas fa-heart"></i>
                                Locurile Mele Favorite
                            </a>
                            <a href="suggest.php" class="btn btn-accent w-full">
                                <i class="fas fa-plus"></i>
                                Sugerează un Loc
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="flex items-center gap-2">
                            <i class="fas fa-chart-bar text-secondary"></i>
                            Statistici
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Locuri favorite</span>
                                <span class="font-bold text-primary">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Evenimente salvate</span>
                                <span class="font-bold text-accent">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Sugestii trimise</span>
                                <span class="font-bold text-secondary">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Recenzii scrise</span>
                                <span class="font-bold text-warning">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="mt-12">
                <h2 class="mb-6">Activitate Recentă</h2>
                <div class="card">
                    <div class="card-body">
                        <div class="text-center py-8">
                            <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-gray-500 mb-2">Nicio activitate încă</h3>
                            <p class="text-gray-400 mb-6">Începe să explorezi Chișinăul pentru a vedea activitatea ta aici.</p>
                            <a href="places.php" class="btn btn-primary">
                                <i class="fas fa-compass"></i>
                                Începe Explorarea
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/main.js"></script>
</body>
</html>

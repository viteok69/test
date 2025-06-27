<?php
require_once '../config/config.php';
require_once 'AuthService.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../dashboard.php');
}

$error = '';
$success = getFlashMessage('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de securitate invalid.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (!empty($email) && !empty($password)) {
            $authService = new AuthService();
            $result = $authService->login($email, $password);
            
            if ($result['success']) {
                flashMessage('success', 'Bun venit înapoi!');
                redirect('../dashboard.php');
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Vă rugăm să completați toate câmpurile.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conectare - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Conectează-te la contul tău pentru a accesa ghidul tinerilor din Chișinău.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="navbar-brand">
                <i class="fas fa-map-marked-alt"></i>
                Ghidul Tinerilor
            </a>
            
            <div class="flex items-center gap-4">
                <button class="theme-toggle" onclick="toggleTheme()" title="Schimbă tema">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Înregistrare
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <section class="py-20">
        <div class="container">
            <div class="flex justify-center">
                <div class="w-full" style="max-width: 400px;">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-8">
                                <div class="mb-4">
                                    <i class="fas fa-sign-in-alt text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h1 class="text-2xl font-bold mb-2">Bun venit înapoi!</h1>
                                <p class="text-gray-600">Conectează-te la contul tău</p>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email
                                    </label>
                                    <input type="email" 
                                           class="form-input" 
                                           id="email" 
                                           name="email" 
                                           placeholder="exemplu@email.com"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i>
                                        Parola
                                    </label>
                                    <input type="password" 
                                           class="form-input" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Introdu parola"
                                           required>
                                </div>

                                <button type="submit" class="btn btn-primary w-full btn-lg">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Conectează-te
                                </button>
                            </form>

                            <div class="text-center mt-6">
                                <p class="text-gray-600">
                                    Nu ai cont? 
                                    <a href="register.php" class="text-primary font-medium hover:underline">
                                        Înregistrează-te aici
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>
</html>

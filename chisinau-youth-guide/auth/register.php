<?php
require_once '../config/config.php';
require_once 'AuthService.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de securitate invalid.';
    } else {
        $data = [
            'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
            'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
            'username' => sanitizeInput($_POST['username'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'age' => (int)($_POST['age'] ?? 0),
            'city' => sanitizeInput($_POST['city'] ?? '')
        ];
        
        $authService = new AuthService();
        $result = $authService->register($data);
        
        if ($result['success']) {
            $success = $result['message'];
            // Clear form data
            $_POST = [];
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Creează-ți un cont pentru a accesa ghidul tinerilor din Chișinău.">
    
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
                <a href="login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    Conectare
                </a>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <section class="py-20">
        <div class="container">
            <div class="flex justify-center">
                <div class="w-full" style="max-width: 600px;">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-8">
                                <div class="mb-4">
                                    <i class="fas fa-user-plus text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h1 class="text-2xl font-bold mb-2">Alătură-te comunității!</h1>
                                <p class="text-gray-600">Creează-ți un cont pentru a descoperi Chișinăul</p>
                            </div>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>
                                        <?php foreach ($errors as $error): ?>
                                            <div><?php echo $error; ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo $success; ?>
                                    <a href="login.php" class="btn btn-primary btn-sm ml-4">
                                        Conectează-te acum
                                    </a>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">
                                            <i class="fas fa-user"></i>
                                            Prenume *
                                        </label>
                                        <input type="text" 
                                               class="form-input" 
                                               id="first_name" 
                                               name="first_name" 
                                               placeholder="Ion"
                                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="last_name" class="form-label">
                                            <i class="fas fa-user"></i>
                                            Nume *
                                        </label>
                                        <input type="text" 
                                               class="form-input" 
                                               id="last_name" 
                                               name="last_name" 
                                               placeholder="Popescu"
                                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" 
                                               required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-at"></i>
                                        Nume utilizator *
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           id="username" 
                                           name="username" 
                                           placeholder="ion_popescu"
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                           required>
                                    <small class="text-gray-500">Doar litere, cifre și underscore (_)</small>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email *
                                    </label>
                                    <input type="email" 
                                           class="form-input" 
                                           id="email" 
                                           name="email" 
                                           placeholder="ion@exemplu.com"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                           required>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label for="age" class="form-label">
                                            <i class="fas fa-birthday-cake"></i>
                                            Vârsta (opțional)
                                        </label>
                                        <input type="number" 
                                               class="form-input" 
                                               id="age" 
                                               name="age" 
                                               min="14" 
                                               max="35"
                                               placeholder="25"
                                               value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="city" class="form-label">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Oraș (opțional)
                                        </label>
                                        <input type="text" 
                                               class="form-input" 
                                               id="city" 
                                               name="city" 
                                               placeholder="Chișinău"
                                               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="form-group">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Parola *
                                        </label>
                                        <input type="password" 
                                               class="form-input" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Minim 6 caractere"
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock"></i>
                                            Confirmă parola *
                                        </label>
                                        <input type="password" 
                                               class="form-input" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               placeholder="Repetă parola"
                                               required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-full btn-lg">
                                    <i class="fas fa-user-plus"></i>
                                    Creează contul
                                </button>
                            </form>

                            <div class="text-center mt-6">
                                <p class="text-gray-600">
                                    Ai deja cont? 
                                    <a href="login.php" class="text-primary font-medium hover:underline">
                                        Conectează-te aici
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

<?php
require_once 'config/database.php';

$success = false;
$error = '';

try {
    // Try to connect to MySQL server first
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('database/schema.sql');
    
    if ($sql === false) {
        throw new Exception('Nu pot citi fișierul database/schema.sql');
    }
    
    // Execute SQL
    $pdo->exec($sql);
    $success = true;
    
} catch (Exception $e) {
    $error = $e->getMessage();
    error_log("Setup error: " . $error);
}
?>

<!DOCTYPE html>
<html lang="ro" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Chișinău Guide</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold mb-4">
                <i class="fas fa-cog text-primary"></i>
                Setup Chișinău Guide
            </h1>
            <p class="text-xl text-gray-600">Configurarea bazei de date</p>
        </div>

        <div class="max-w-2xl mx-auto">
            <?php if ($success): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-6xl mb-4">✅</div>
                        <h2 class="text-2xl font-bold text-green-600 mb-4">Setup Complet!</h2>
                        <p class="text-gray-600 mb-6">
                            Baza de date a fost configurată cu succes cu peste 50 de locuri din Chișinău!
                        </p>
                        <div class="flex gap-4 justify-center">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-home"></i>
                                Vezi Site-ul
                            </a>
                            <a href="admin.php" class="btn btn-accent btn-lg">
                                <i class="fas fa-cog"></i>
                                Panou Admin
                            </a>
                        </div>
                        
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <h3 class="font-bold mb-2">🎉 Ce poți face acum:</h3>
                            <ul class="text-left text-sm space-y-1">
                                <li>✅ Caută prin 50+ locuri din Chișinău</li>
                                <li>✅ Filtrează după categorii</li>
                                <li>✅ Comută între modul întunecat/luminos</li>
                                <li>✅ Adaugă locuri noi prin panoul admin</li>
                                <li>✅ Toate datele sunt salvate permanent</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-6xl mb-4">❌</div>
                        <h2 class="text-2xl font-bold text-red-600 mb-4">Eroare Setup</h2>
                        <p class="text-gray-600 mb-6">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg mb-6 text-left">
                            <h3 class="font-bold mb-2">🔧 Verifică următoarele:</h3>
                            <ul class="text-sm space-y-1">
                                <li>• MySQL este pornit (XAMPP/WAMP/MAMP)</li>
                                <li>• Username: root, Password: (gol)</li>
                                <li>• Fișierul database/schema.sql există</li>
                                <li>• Ai permisiuni de creare baze de date</li>
                            </ul>
                        </div>
                        
                        <a href="setup.php" class="btn btn-primary">
                            <i class="fas fa-redo"></i>
                            Încearcă din nou
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

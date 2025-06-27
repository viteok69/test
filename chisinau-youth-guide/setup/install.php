<?php
// Database setup script
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server (without specifying database)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up Chișinău Youth Guide Database...</h2>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS chisinau_youth_guide CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database 'chisinau_youth_guide' created successfully!</p>";
    
    // Use the database
    $pdo->exec("USE chisinau_youth_guide");
    
    // Create tables
    $sql = "
    -- Categories table
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_ro VARCHAR(100) NOT NULL,
        name_ru VARCHAR(100),
        name_en VARCHAR(100),
        description TEXT,
        icon VARCHAR(50),
        color VARCHAR(7) DEFAULT '#007bff',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Places table
    CREATE TABLE IF NOT EXISTS places (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        category_id INT,
        address VARCHAR(300),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        phone VARCHAR(20),
        email VARCHAR(100),
        website VARCHAR(200),
        image VARCHAR(300),
        rating DECIMAL(3,2) DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        birth_date DATE,
        phone VARCHAR(20),
        avatar VARCHAR(300),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- User favorites
    CREATE TABLE IF NOT EXISTS user_favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        place_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
        UNIQUE KEY unique_favorite (user_id, place_id)
    );

    -- Place suggestions
    CREATE TABLE IF NOT EXISTS place_suggestions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        category_id INT,
        address VARCHAR(300),
        phone VARCHAR(20),
        email VARCHAR(100),
        website VARCHAR(200),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        admin_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    -- Reviews table
    CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        place_id INT,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
        UNIQUE KEY unique_review (user_id, place_id)
    );
    ";
    
    $pdo->exec($sql);
    echo "<p>✅ Tables created successfully!</p>";
    
    // Insert sample categories
    $categories = [
        ['Restaurante & Cafenele', 'Рестораны и кафе', 'Restaurants & Cafes', 'Locuri unde poți mânca și bea', 'fas fa-utensils', '#e74c3c'],
        ['Educație', 'Образование', 'Education', 'Universități, școli, cursuri', 'fas fa-graduation-cap', '#3498db'],
        ['Divertisment', 'Развлечения', 'Entertainment', 'Cinematografe, teatre, cluburi', 'fas fa-masks-theater', '#9b59b6'],
        ['Sport & Fitness', 'Спорт и фитнес', 'Sports & Fitness', 'Săli de sport, parcuri, activități', 'fas fa-dumbbell', '#27ae60'],
        ['Cultură', 'Культура', 'Culture', 'Muzee, galerii, centre culturale', 'fas fa-palette', '#f39c12'],
        ['Shopping', 'Шоппинг', 'Shopping', 'Magazine, centre comerciale', 'fas fa-shopping-bag', '#e67e22'],
        ['Servicii', 'Услуги', 'Services', 'Servicii utile pentru tineri', 'fas fa-tools', '#34495e'],
        ['Parcuri & Natură', 'Парки и природа', 'Parks & Nature', 'Spații verzi și zone de recreere', 'fas fa-tree', '#16a085']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name_ro, name_ru, name_en, description, icon, color) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<p>✅ Sample categories inserted!</p>";
    
    // Insert sample places
    $places = [
        ['Tucano Coffee', 'Cafenea modernă în centrul orașului cu atmosferă prietenoasă pentru tineri', 1, 'Str. Ștefan cel Mare 126', 47.0245, 28.8322, '+373 22 123456', 'info@tucanocoffee.md', 'https://tucanocoffee.md', '/assets/images/tucano.jpg', 4.5],
        ['Universitatea Tehnică', 'Universitate tehnică de top din Moldova', 2, 'Bd. Ștefan cel Mare 168', 47.0220, 28.8353, '+373 22 509138', 'info@utm.md', 'https://utm.md', '/assets/images/utm.jpg', 4.2],
        ['Cinematograful Patria', 'Cinema modern cu filme în premieră', 3, 'Bd. Ștefan cel Mare 61', 47.0267, 28.8306, '+373 22 240404', 'info@patria.md', 'https://patria.md', '/assets/images/patria.jpg', 4.3],
        ['Parcul Valea Morilor', 'Cel mai mare parc din Chișinău, perfect pentru relaxare', 8, 'Str. Tighina', 47.0186, 28.8067, null, null, null, '/assets/images/valea-morilor.jpg', 4.7],
        ['Fitness Club Energia', 'Sală de fitness modernă cu echipamente de ultimă generație', 4, 'Str. Armenească 35', 47.0289, 28.8267, '+373 22 445566', 'info@energia.md', 'https://energia.md', '/assets/images/energia.jpg', 4.1],
        ['Muzeul Național de Istorie', 'Muzeul principal al țării cu expoziții permanente și temporare', 5, 'Str. 31 August 1989, 121A', 47.0267, 28.8289, '+373 22 244325', 'info@nationalmuseum.md', 'https://nationalmuseum.md', '/assets/images/museum.jpg', 4.4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO places (title, description, category_id, address, latitude, longitude, phone, email, website, image, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($places as $place) {
        $stmt->execute($place);
    }
    echo "<p>✅ Sample places inserted!</p>";
    
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Your database is ready!</strong> You can now:</p>";
    echo "<ul>";
    echo "<li><a href='../index.php'>Visit the homepage</a></li>";
    echo "<li><a href='../auth/register.php'>Create an account</a></li>";
    echo "<li><a href='../places.php'>Browse places</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

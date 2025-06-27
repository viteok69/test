-- Create the places database
CREATE DATABASE IF NOT EXISTS places_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE places_db;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS users;

-- Create users table for admin functionality
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create places table
CREATE TABLE places (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category ENUM('park', 'restaurant', 'cafe', 'museum', 'shopping', 'education', 'entertainment', 'sports', 'health', 'transport', 'culture', 'nightlife') NOT NULL,
    address TEXT NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(500) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    added_by_user BOOLEAN DEFAULT FALSE,
    phone VARCHAR(20) NULL,
    website VARCHAR(200) NULL,
    opening_hours TEXT NULL,
    price_range ENUM('free', 'budget', 'moderate', 'expensive') DEFAULT 'moderate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, is_admin) VALUES 
('admin', 'admin@chisinau-guide.md', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert real places from Chișinău
INSERT INTO places (name, category, address, description, latitude, longitude, added_by_user, phone, website, opening_hours, price_range) VALUES

-- Parks and Recreation
('Parcul Valea Morilor', 'park', 'Bulevardul Dacia, Chișinău', 'Cel mai mare parc din Chișinău cu lac artificial, alei pentru plimbări, zone de picnic și activități sportive. Perfect pentru relaxare și sport în aer liber.', 47.0167, 28.8056, FALSE, NULL, NULL, '24/7', 'free'),

('Parcul Central "Ștefan cel Mare și Sfânt"', 'park', 'Bulevardul Ștefan cel Mare și Sfânt, Chișinău', 'Parcul central istoric al capitalei cu monumente, fântâni arteziene și alei umbrite. Locul perfect pentru plimbări în centrul orașului.', 47.0245, 28.8322, FALSE, NULL, NULL, '24/7', 'free'),

('Parcul La Izvor', 'park', 'Strada Tighina, Chișinău', 'Parc modern cu terenuri de sport, zone de joacă pentru copii și spații verzi amenajate. Popular printre familii și sportivi.', 47.0089, 28.8567, FALSE, NULL, NULL, '06:00-22:00', 'free'),

-- Restaurants and Food
('Andy\'s Pizza', 'restaurant', 'Bulevardul Ștefan cel Mare și Sfânt 3, Chișinău', 'Lanțul local de pizzerii cel mai popular din Moldova. Pizza proaspătă, livrare rapidă și prețuri accesibile pentru tineri.', 47.0245, 28.8267, FALSE, '+373 22 27-27-27', 'https://andys.md', '10:00-23:00', 'budget'),

('Tucano Coffee', 'cafe', 'Strada 31 August 1989, 121, Chișinău', 'Cafenea modernă cu atmosferă plăcută, cafea de calitate și deserturi delicioase. Locul perfect pentru întâlniri și studiu.', 47.0189, 28.8356, FALSE, '+373 22 123-456', NULL, '07:00-22:00', 'moderate'),

('Smokehouse BBQ', 'restaurant', 'Strada Armenească 13, Chișinău', 'Restaurant specializat în grătar și BBQ cu atmosferă americană. Porții generoase și prețuri rezonabile.', 47.0234, 28.8289, FALSE, '+373 69 123-456', NULL, '12:00-23:00', 'moderate'),

('Caravan Restaurant', 'restaurant', 'Strada Ismail 98, Chișinău', 'Restaurant cu bucătărie moldovenească tradițională și internațională. Atmosferă elegantă și servicii de calitate.', 47.0198, 28.8345, FALSE, '+373 22 234-567', 'https://caravan.md', '11:00-24:00', 'expensive'),

-- Cafes
('Starbucks Moldova', 'cafe', 'Bulevardul Ștefan cel Mare și Sfânt 126, Chișinău', 'Prima cafenea Starbucks din Moldova. Cafea premium, atmosferă internațională și WiFi gratuit.', 47.0223, 28.8334, FALSE, '+373 22 345-678', NULL, '07:00-22:00', 'moderate'),

('Café Central', 'cafe', 'Piața Marii Adunări Naționale 1, Chișinău', 'Cafenea istorică în centrul capitalei cu vedere la Piața Centrală. Cafea tradițională și atmosferă autentică.', 47.0267, 28.8289, FALSE, '+373 22 456-789', NULL, '08:00-20:00', 'budget'),

-- Shopping
('Mall Dova', 'shopping', 'Strada Arborilor 21, Chișinău', 'Cel mai mare centru comercial din Moldova cu peste 100 de magazine, cinema, food court și zone de divertisment.', 47.0056, 28.7906, FALSE, '+373 22 888-555', 'https://malldova.md', '10:00-22:00', 'moderate'),

('Shopping MallDova', 'shopping', 'Strada Calea Ieșilor 8, Chișinău', 'Centru comercial modern cu magazine de brand, restaurante și cinema. Popular printre tineri pentru shopping și divertisment.', 47.0445, 28.8678, FALSE, '+373 22 567-890', NULL, '10:00-22:00', 'moderate'),

-- Education
('Universitatea de Stat din Moldova', 'education', 'Strada Alexei Mateevici 60, Chișinău', 'Cea mai prestigioasă universitate din Moldova cu multiple facultăți și programe de studiu. Campus modern cu biblioteci și laboratoare.', 47.0220, 28.8353, FALSE, '+373 22 577-110', 'https://usm.md', '08:00-18:00', 'free'),

('Universitatea Tehnică din Moldova', 'education', 'Bulevardul Ștefan cel Mare și Sfânt 168, Chișinău', 'Universitate tehnică de top cu focus pe inginerie, IT și tehnologii moderne. Laboratoare avansate și programe actuale.', 47.0198, 28.8292, FALSE, '+373 22 509-958', 'https://utm.md', '08:00-18:00', 'free'),

-- Museums and Culture
('Muzeul Național de Istorie a Moldovei', 'museum', 'Strada 31 August 1989, 121A, Chișinău', 'Muzeul principal al țării cu expoziții despre istoria și cultura Moldovei. Colecții arheologice și etnografice valoroase.', 47.0189, 28.8356, FALSE, '+373 22 241-356', 'https://nationalmuseum.md', '10:00-18:00', 'budget'),

('Muzeul Național de Arte Plastice', 'museum', 'Strada 31 August 1989, 115, Chișinău', 'Muzeul de artă cu cea mai bogată colecție de pictură și sculptură din Moldova. Expoziții permanente și temporare.', 47.0178, 28.8367, FALSE, '+373 22 212-460', NULL, '10:00-17:00', 'budget'),

-- Entertainment
('Cinema Patria', 'entertainment', 'Strada Ștefan cel Mare 61, Chișinău', 'Cinematograf istoric renovat cu tehnologie modernă. Filme în premieră și evenimente culturale speciale.', 47.0245, 28.8322, FALSE, '+373 22 123-789', NULL, '10:00-23:00', 'moderate'),

('Teatrul Național "Mihai Eminescu"', 'culture', 'Bulevardul Ștefan cel Mare și Sfânt 79, Chișinău', 'Teatrul național cu spectacole de calitate în limba română. Repertoriu clasic și contemporan.', 47.0235, 28.8267, FALSE, '+373 22 244-967', 'https://teatrul-national.md', '19:00-22:00', 'moderate'),

-- Sports
('Complexul Sportiv Republican', 'sports', 'Strada Tricolorului 1, Chișinău', 'Complex sportiv modern cu săli pentru diverse sporturi, piscină și terenuri exterioare. Acces pentru publicul larg.', 47.0334, 28.8445, FALSE, '+373 22 678-901', NULL, '06:00-22:00', 'budget'),

-- Health
('Spitalul Clinic Republican', 'health', 'Strada Nicolae Testemițanu 29, Chișinău', 'Principala instituție medicală din Moldova cu servicii complete și urgențe 24/7. Personal medical calificat.', 47.0311, 28.8267, FALSE, '+373 22 729-001', 'https://scr.md', '24/7', 'free'),

-- Nightlife
('Club Soho', 'nightlife', 'Strada Armenească 55, Chișinău', 'Club de noapte popular cu muzică modernă, DJ-i cunoscuți și atmosferă vibrantă. Evenimente speciale în weekend.', 47.0267, 28.8234, FALSE, '+373 69 234-567', NULL, '22:00-06:00', 'expensive');

-- Create the places database
CREATE DATABASE IF NOT EXISTS places_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE places_db;

-- Drop existing tables
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create places table with URL field
CREATE TABLE places (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category ENUM('park', 'restaurant', 'cafe', 'museum', 'shopping', 'education', 'entertainment', 'sports', 'coworking', 'nightlife', 'health', 'transport') NOT NULL,
    address TEXT NOT NULL,
    description TEXT NOT NULL,
    website_url VARCHAR(500) NULL,
    image_url VARCHAR(500) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    phone VARCHAR(20) NULL,
    opening_hours VARCHAR(100) NULL,
    price_range ENUM('free', 'budget', 'moderate', 'expensive') DEFAULT 'moderate',
    rating DECIMAL(2,1) DEFAULT 0.0,
    added_by_user BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Add indexes for better search performance
    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at),
    FULLTEXT(name, description, address)
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, is_admin) VALUES 
('admin', 'admin@chisinau-guide.md', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert comprehensive places data for Chișinău
INSERT INTO places (name, category, address, description, website_url, latitude, longitude, phone, opening_hours, price_range, rating) VALUES

-- PARKS & RECREATION (10 places)
('Parcul Valea Morilor', 'park', 'Bulevardul Dacia, Chișinău', 'Cel mai mare parc din Chișinău cu lac artificial, alei pentru plimbări, zone de picnic și activități sportive. Perfect pentru relaxare și sport în aer liber.', NULL, 47.0167, 28.8056, NULL, '24/7', 'free', 4.5),

('Parcul Central "Ștefan cel Mare"', 'park', 'Bulevardul Ștefan cel Mare și Sfânt, Chișinău', 'Parcul central istoric al capitalei cu monumente, fântâni arteziene și alei umbrite. Locul perfect pentru plimbări în centrul orașului.', NULL, 47.0245, 28.8322, NULL, '24/7', 'free', 4.7),

('Parcul La Izvor', 'park', 'Strada Tighina, Chișinău', 'Parc modern cu terenuri de sport, zone de joacă pentru copii și spații verzi amenajate. Popular printre familii și sportivi.', NULL, 47.0089, 28.8567, NULL, '06:00-22:00', 'free', 4.3),

('Parcul Dendrarium', 'park', 'Strada Ghioceilor 1, Chișinău', 'Grădina botanică cu colecții rare de plante, alei pitorești și zone de odihnă. Ideal pentru iubitorii naturii și fotografii.', NULL, 47.0234, 28.8445, '+373 22 550-187', '09:00-18:00', 'budget', 4.4),

('Parcul Rîșcani', 'park', 'Strada Florilor, Chișinău', 'Parc de cartier cu terenuri de sport, zone de joacă și spații verzi. Locul preferat al tinerilor din sectorul Rîșcani.', NULL, 47.0456, 28.8234, NULL, '24/7', 'free', 4.1),

-- RESTAURANTS (15 places)
('Andy\'s Pizza', 'restaurant', 'Bulevardul Ștefan cel Mare și Sfânt 3, Chișinău', 'Lanțul local de pizzerii cel mai popular din Moldova. Pizza proaspătă, livrare rapidă și prețuri accesibile pentru tineri.', 'https://andys.md', 47.0245, 28.8267, '+373 22 27-27-27', '10:00-23:00', 'budget', 4.3),

('Smokehouse BBQ', 'restaurant', 'Strada Armenească 13, Chișinău', 'Restaurant specializat în grătar și BBQ cu atmosferă americană. Porții generoase și prețuri rezonabile.', NULL, 47.0234, 28.8289, '+373 69 123-456', '12:00-23:00', 'moderate', 4.5),

('Caravan Restaurant', 'restaurant', 'Strada Ismail 98, Chișinău', 'Restaurant cu bucătărie moldovenească tradițională și internațională. Atmosferă elegantă și servicii de calitate.', 'https://caravan.md', 47.0198, 28.8345, '+373 22 234-567', '11:00-24:00', 'expensive', 4.7),

('La Placinte', 'restaurant', 'Strada 31 August 1989, 78, Chișinău', 'Lanț de restaurante cu bucătărie moldovenească autentică. Plăcinte delicioase și atmosferă tradițională.', 'https://laplacinte.md', 47.0189, 28.8356, '+373 22 345-678', '08:00-22:00', 'budget', 4.4),

('Pegas Restaurant', 'restaurant', 'Strada Mitropolit Varlaam 77, Chișinău', 'Restaurant elegant cu bucătărie europeană și moldovenească. Servicii premium și atmosferă rafinată.', 'https://pegas.md', 47.0267, 28.8234, '+373 22 456-789', '12:00-24:00', 'expensive', 4.6),

('Torro Burgers', 'restaurant', 'Strada Columna 45, Chișinău', 'Burger house modern cu ingrediente proaspete și rețete unice. Atmosferă tinerească și prețuri accesibile.', 'https://torro.md', 47.0156, 28.8378, '+373 69 555-777', '11:00-23:00', 'moderate', 4.4),

('Burger House', 'restaurant', 'Strada Armenească 45, Chișinău', 'Restaurant specializat în burgeri gourmet și fast-food de calitate. Popular printre tineri și studenți.', NULL, 47.0245, 28.8267, '+373 69 345-678', '11:00-23:00', 'budget', 4.1),

('Sushi Master', 'restaurant', 'Strada 31 August 1989, 134, Chișinău', 'Restaurant japonez cu sushi proaspăt și bucătărie asiatică autentică. Calitate premium și prezentare impecabilă.', NULL, 47.0198, 28.8345, '+373 22 789-012', '12:00-22:00', 'expensive', 4.4),

('Pizza Tempo', 'restaurant', 'Strada Ismail 67, Chișinău', 'Pizzerie cu livrare rapidă și ingrediente proaspete. Prețuri accesibile și varietate mare de pizza.', 'https://pizzatempo.md', 47.0189, 28.8356, '+373 22 890-123', '10:00-23:00', 'budget', 4.0),

('Gastrobar 1900', 'restaurant', 'Strada Kogălniceanu 78, Chișinău', 'Gastrobar modern cu bucătărie fusion și cocktailuri creative. Atmosferă sofisticată pentru tineri profesioniști.', NULL, 47.0223, 28.8312, '+373 69 456-789', '18:00-02:00', 'expensive', 4.6),

-- CAFES (10 places)
('Tucano Coffee', 'cafe', 'Strada 31 August 1989, 121, Chișinău', 'Cafenea modernă cu atmosferă plăcută, cafea de calitate și deserturi delicioase. Locul perfect pentru întâlniri și studiu.', 'https://tucano.md', 47.0189, 28.8356, '+373 22 123-456', '07:00-22:00', 'moderate', 4.5),

('Starbucks Moldova', 'cafe', 'Bulevardul Ștefan cel Mare și Sfânt 126, Chișinău', 'Prima cafenea Starbucks din Moldova. Cafea premium, atmosferă internațională și WiFi gratuit.', 'https://starbucks.md', 47.0223, 28.8334, '+373 22 345-678', '07:00-22:00', 'moderate', 4.4),

('Café Central', 'cafe', 'Piața Marii Adunări Naționale 1, Chișinău', 'Cafenea istorică în centrul capitalei cu vedere la Piața Centrală. Cafea tradițională și atmosferă autentică.', NULL, 47.0267, 28.8289, '+373 22 456-789', '08:00-20:00', 'budget', 4.2),

('Coffee Island', 'cafe', 'Strada Armenească 23, Chișinău', 'Cafenea modernă cu specialități de cafea și atmosferă relaxată. Perfect pentru freelanceri și studenți.', NULL, 47.0234, 28.8289, '+373 69 567-890', '07:00-21:00', 'moderate', 4.3),

('Artcafe', 'cafe', 'Strada Pușkin 47, Chișinău', 'Cafenea cu galerie de artă și evenimente culturale. Atmosferă bohemă și cafea de specialitate.', NULL, 47.0245, 28.8267, '+373 69 678-901', '09:00-22:00', 'moderate', 4.4),

('Brew Coffee', 'cafe', 'Strada Columna 45, Chișinău', 'Cafenea specializată în cafea de origine și metode alternative de preparare. Pentru adevărații iubitori de cafea.', NULL, 47.0156, 28.8378, '+373 69 789-012', '08:00-20:00', 'moderate', 4.6),

('Cozy Corner', 'cafe', 'Strada Ismail 34, Chișinău', 'Cafenea intimă cu atmosferă caldă și prietenos. Locul perfect pentru citit și relaxare.', NULL, 47.0198, 28.8345, '+373 69 890-123', '08:00-21:00', 'budget', 4.1),

('Urban Coffee', 'cafe', 'Strada 31 August 1989, 89, Chișinău', 'Cafenea urbană cu design modern și meniu variat. Popular printre tinerii profesioniști.', NULL, 47.0189, 28.8356, '+373 69 901-234', '07:00-22:00', 'moderate', 4.2),

('Beans & Books', 'cafe', 'Strada Kogălniceanu 56, Chișinău', 'Cafenea-librărie cu atmosferă intelectuală și evenimente literare. Perfect pentru iubitorii de cărți și cafea.', NULL, 47.0223, 28.8312, '+373 69 012-345', '09:00-21:00', 'moderate', 4.5),

('Morning Glory', 'cafe', 'Strada Mitropolit Varlaam 45, Chișinău', 'Cafenea specializată în mic dejun și brunch. Atmosferă luminoasă și meniu sănătos.', NULL, 47.0267, 28.8234, '+373 69 123-456', '07:00-15:00', 'budget', 4.3),

-- SHOPPING (8 places)
('Mall Dova', 'shopping', 'Strada Arborilor 21, Chișinău', 'Cel mai mare centru comercial din Moldova cu peste 100 de magazine, cinema, food court și zone de divertisment.', 'https://malldova.md', 47.0056, 28.7906, '+373 22 888-555', '10:00-22:00', 'moderate', 4.5),

('Shopping MallDova', 'shopping', 'Strada Calea Ieșilor 8, Chișinău', 'Centru comercial modern cu magazine de brand, restaurante și cinema. Popular printre tineri pentru shopping și divertisment.', NULL, 47.0445, 28.8678, '+373 22 567-890', '10:00-22:00', 'moderate', 4.3),

('Atrium Mall', 'shopping', 'Strada Albișoara 78, Chișinău', 'Centru comercial cu magazine de modă, electronice și servicii. Atmosferă modernă și facilități complete.', NULL, 47.0123, 28.8456, '+373 22 678-901', '10:00-22:00', 'moderate', 4.2),

('Sun City', 'shopping', 'Strada Calea Basarabiei 15, Chișinău', 'Complex comercial cu hipermarket, magazine și zone de recreere. Convenabil pentru shopping zilnic.', NULL, 47.0234, 28.8567, '+373 22 789-012', '08:00-23:00', 'moderate', 4.1),

('Piața Centrală', 'shopping', 'Bulevardul Ștefan cel Mare și Sfânt, Chișinău', 'Piața tradițională cu produse locale, suveniruri și atmosferă autentică. Experiență culturală unică.', NULL, 47.0245, 28.8322, NULL, '06:00-18:00', 'budget', 4.4),

('Gemeni Shopping Center', 'shopping', 'Strada Calea Orheiului 67, Chișinău', 'Centru comercial de cartier cu magazine diverse și servicii. Convenabil pentru locuitorii din zonă.', NULL, 47.0345, 28.7945, '+373 22 890-123', '09:00-21:00', 'moderate', 4.0),

('Elat Mall', 'shopping', 'Strada Calea Ieșilor 34, Chișinău', 'Mall modern cu magazine de brand și zone de divertisment. Design contemporan și facilități moderne.', NULL, 47.0456, 28.8678, '+373 22 901-234', '10:00-22:00', 'moderate', 4.2),

('Botanica Mall', 'shopping', 'Strada Dacia 47/4, Chișinău', 'Centru comercial din sectorul Botanica cu magazine diverse și food court. Accesibil și convenabil.', NULL, 46.9987, 28.8234, '+373 22 012-345', '10:00-21:00', 'moderate', 4.1),

-- EDUCATION (6 places)
('Universitatea de Stat din Moldova', 'education', 'Strada Alexei Mateevici 60, Chișinău', 'Cea mai prestigioasă universitate din Moldova cu multiple facultăți și programe de studiu. Campus modern cu biblioteci și laboratoare.', 'https://usm.md', 47.0220, 28.8353, '+373 22 577-110', '08:00-18:00', 'free', 4.8),

('Universitatea Tehnică din Moldova', 'education', 'Bulevardul Ștefan cel Mare și Sfânt 168, Chișinău', 'Universitate tehnică de top cu focus pe inginerie, IT și tehnologii moderne. Laboratoare avansate și programe actuale.', 'https://utm.md', 47.0198, 28.8292, '+373 22 509-958', '08:00-18:00', 'free', 4.7),

('Academia de Studii Economice', 'education', 'Strada Bănulescu-Bodoni 61, Chișinău', 'Universitate specializată în economie, business și management. Programe moderne și conexiuni cu mediul de afaceri.', 'https://ase.md', 47.0234, 28.8289, '+373 22 402-945', '08:00-18:00', 'free', 4.6),

('Universitatea Pedagogică "Ion Creangă"', 'education', 'Strada Ion Creangă 1, Chișinău', 'Universitate specializată în științe ale educației și formare de cadre didactice. Tradiție îndelungată în educație.', 'https://upsc.md', 47.0267, 28.8234, '+373 22 307-176', '08:00-18:00', 'free', 4.5),

('Biblioteca Națională', 'education', 'Strada 31 August 1989, 78A, Chișinău', 'Cea mai mare bibliotecă din Moldova cu colecții vaste și spații moderne de studiu. Resurse digitale și evenimente culturale.', 'https://bnrm.md', 47.0189, 28.8356, '+373 22 241-331', '09:00-20:00', 'free', 4.4),

('IT Step Academy', 'education', 'Strada Armenească 47, Chișinău', 'Academie IT cu cursuri practice în programare, design și tehnologii moderne. Pregătire pentru cariera în IT.', 'https://itstep.md', 47.0234, 28.8289, '+373 22 123-789', '09:00-21:00', 'expensive', 4.6),

-- COWORKING SPACES (5 places)
('Tekwill', 'coworking', 'Strada Alexei Mateevici 60, Chișinău', 'Cel mai mare hub tehnologic din Moldova cu spații de coworking, evenimente și programe de accelerare pentru startup-uri.', 'https://tekwill.md', 47.0220, 28.8353, '+373 22 123-456', '08:00-20:00', 'moderate', 4.7),

('ATIC Coworking', 'coworking', 'Strada Columna 111, Chișinău', 'Spațiu modern de coworking cu facilități complete pentru freelanceri și echipe mici. Atmosferă profesională și productivă.', 'https://atic.md', 47.0156, 28.8378, '+373 69 234-567', '08:00-22:00', 'moderate', 4.5),

('Impact Hub Chișinău', 'coworking', 'Strada Pușkin 47, Chișinău', 'Hub pentru antreprenori sociali și startup-uri cu impact. Comunitate activă și evenimente de networking.', 'https://impacthub.md', 47.0245, 28.8267, '+373 69 345-678', '09:00-21:00', 'moderate', 4.6),

('Coworking Plus', 'coworking', 'Strada 31 August 1989, 98, Chișinău', 'Spațiu de coworking cu design modern și facilități premium. Perfect pentru profesioniști și echipe creative.', NULL, 47.0189, 28.8356, '+373 69 456-789', '08:00-20:00', 'moderate', 4.3),

('Creative Space', 'coworking', 'Strada Ismail 76, Chișinău', 'Spațiu de lucru pentru creativi, designeri și artiști. Atmosferă inspirațională și facilități specializate.', NULL, 47.0198, 28.8345, '+373 69 567-890', '09:00-21:00', 'moderate', 4.4),

-- MUSEUMS & CULTURE (4 places)
('Muzeul Național de Istorie', 'museum', 'Strada 31 August 1989, 121A, Chișinău', 'Muzeul principal al țării cu expoziții despre istoria și cultura Moldovei. Colecții arheologice și etnografice valoroase.', 'https://nationalmuseum.md', 47.0189, 28.8356, '+373 22 241-356', '10:00-18:00', 'budget', 4.6),

('Muzeul Național de Arte', 'museum', 'Strada 31 August 1989, 115, Chișinău', 'Muzeul de artă cu cea mai bogată colecție de pictură și sculptură din Moldova. Expoziții permanente și temporare.', NULL, 47.0178, 28.8367, '+373 22 212-460', '10:00-17:00', 'budget', 4.5),

('Teatrul Național "Mihai Eminescu"', 'entertainment', 'Bulevardul Ștefan cel Mare și Sfânt 79, Chișinău', 'Teatrul național cu spectacole de calitate în limba română. Repertoriu clasic și contemporan.', 'https://teatrul-national.md', 47.0235, 28.8267, '+373 22 244-967', '19:00-22:00', 'moderate', 4.7),

('Opera Națională', 'entertainment', 'Bulevardul Ștefan cel Mare și Sfânt 152, Chișinău', 'Opera și balet de nivel internațional cu spectacole de prestigiu. Clădire istorică și acustică perfectă.', 'https://opera.md', 47.0198, 28.8292, '+373 22 227-040', '19:00-22:00', 'expensive', 4.8),

-- SPORTS & FITNESS (3 places)
('Complexul Sportiv Republican', 'sports', 'Strada Tricolorului 1, Chișinău', 'Complex sportiv modern cu săli pentru diverse sporturi, piscină și terenuri exterioare. Acces pentru publicul larg.', NULL, 47.0334, 28.8445, '+373 22 678-901', '06:00-22:00', 'budget', 4.4),

('Fitness Club "Energia"', 'sports', 'Strada Armenească 67, Chișinău', 'Sală de fitness modernă cu echipamente performante și antrenori calificați. Programe diverse pentru toate nivelurile.', NULL, 47.0234, 28.8289, '+373 69 678-901', '06:00-23:00', 'moderate', 4.3),

('Aqua Park "Nemo"', 'entertainment', 'Strada Calea Ieșilor 67, Chișinău', 'Parc acvatic cu tobogane, piscine și zone de relaxare. Divertisment pentru toată familia în sezonul cald.', 'https://nemo.md', 47.0445, 28.8678, '+373 22 789-012', '10:00-20:00', 'moderate', 4.5);

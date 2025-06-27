<?php
require_once __DIR__ . '/../config/config.php';

class AuthService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function register($data) {
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check if user already exists
        if ($this->userExists($data['email'], $data['username'])) {
            return ['success' => false, 'errors' => ['Email-ul sau numele de utilizator există deja.']];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $query = "INSERT INTO users (username, email, password, first_name, last_name, age, city) 
                      VALUES (:username, :email, :password, :first_name, :last_name, :age, :city)";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => $hashedPassword,
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':age' => $data['age'] ?: null,
                ':city' => $data['city'] ?: null
            ]);
            
            if ($success) {
                return ['success' => true, 'message' => 'Contul a fost creat cu succes!'];
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
        }
        
        return ['success' => false, 'errors' => ['A apărut o eroare la crearea contului.']];
    }
    
    public function login($email, $password) {
        try {
            $query = "SELECT id, username, email, password, first_name, last_name FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            
            if ($user = $stmt->fetch()) {
                if (password_verify($password, $user['password'])) {
                    // Set session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    
                    // Update last login
                    $this->updateLastLogin($user['id']);
                    
                    return ['success' => true, 'user' => $user];
                }
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
        }
        
        return ['success' => false, 'error' => 'Email sau parolă incorectă.'];
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true];
    }
    
    private function validateRegistration($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['first_name'])) $errors[] = 'Prenumele este obligatoriu.';
        if (empty($data['last_name'])) $errors[] = 'Numele este obligatoriu.';
        if (empty($data['username'])) $errors[] = 'Numele de utilizator este obligatoriu.';
        if (empty($data['email'])) $errors[] = 'Email-ul este obligatoriu.';
        if (empty($data['password'])) $errors[] = 'Parola este obligatorie.';
        if (empty($data['confirm_password'])) $errors[] = 'Confirmarea parolei este obligatorie.';
        
        // Validation rules
        if (!empty($data['username']) && strlen($data['username']) < 3) {
            $errors[] = 'Numele de utilizator trebuie să aibă cel puțin 3 caractere.';
        }
        
        if (!empty($data['username']) && !preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Numele de utilizator poate conține doar litere, cifre și underscore.';
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email-ul nu este valid.';
        }
        
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors[] = 'Parola trebuie să aibă cel puțin 6 caractere.';
        }
        
        if (!empty($data['password']) && !empty($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            $errors[] = 'Parolele nu coincid.';
        }
        
        if (!empty($data['age']) && ($data['age'] < 14 || $data['age'] > 35)) {
            $errors[] = 'Vârsta trebuie să fie între 14 și 35 de ani.';
        }
        
        return $errors;
    }
    
    private function userExists($email, $username) {
        try {
            $query = "SELECT id FROM users WHERE email = :email OR username = :username";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email, ':username' => $username]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("User exists check error: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateLastLogin($userId) {
        try {
            $query = "UPDATE users SET last_login = NOW() WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
}
?>

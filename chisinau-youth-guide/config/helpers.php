<?php
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeUrl($url) {
    if (empty($url)) {
        return null;
    }
    
    $url = trim($url);
    
    // Add https:// if no protocol is specified
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    
    // Validate and sanitize
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return $url;
}

function validateUrl($url) {
    if (empty($url)) {
        return true; // Empty URLs are allowed
    }
    
    // Add protocol if missing
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    
    // Check if it's a valid URL format
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}
?>

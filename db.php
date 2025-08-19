<?php
// Database connection file
$host = 'localhost';
$dbname = 'dbwco5psp2q3xb';
$username = 'uxhc7qjwxxfub';
$password = 'g4t0vezqttq6';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get user data
function getUserData($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Function to check domain availability (mock function)
function checkDomainAvailability($domain) {
    // Mock availability - in real app, you'd use domain API
    $unavailable_domains = ['google.com', 'facebook.com', 'amazon.com', 'microsoft.com'];
    return !in_array(strtolower($domain), $unavailable_domains);
}

// Function to get domain price
function getDomainPrice($extension) {
    $prices = [
        '.com' => 12.99,
        '.net' => 14.99,
        '.org' => 13.99,
        '.info' => 9.99,
        '.biz' => 11.99,
        '.co' => 24.99
    ];
    return isset($prices[$extension]) ? $prices[$extension] : 15.99;
}
?>

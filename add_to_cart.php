<?php
// Start session and include database
session_start();
require_once 'db.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    sendResponse(false, 'Please login first to add domains to cart');
}

$user_id = $_SESSION['user_id'];

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Only POST requests are allowed');
}

// Get JSON input
$json_input = file_get_contents('php://input');
$input = json_decode($json_input, true);

// Fallback to POST data if JSON fails
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

// Check if we have input data
if (!$input || !isset($input['domain']) || !isset($input['price'])) {
    sendResponse(false, 'Missing domain or price information');
}

// Get and clean input data
$domain = trim(strtolower($input['domain']));
$price = floatval($input['price']);

if (empty($domain) || strpos($domain, '.') === false || $price <= 0) {
    sendResponse(false, 'Invalid domain or price', [
        'received_domain' => $input['domain'] ?? 'not provided',
        'cleaned_domain' => $domain,
        'received_price' => $input['price'] ?? 'not provided',
        'parsed_price' => $price,
        'validation_failed' => [
            'empty_domain' => empty($domain),
            'no_dot_found' => strpos($domain, '.') === false,
            'invalid_price' => $price <= 0
        ]
    ]);
}

// Remove any protocol if present
$domain = str_replace(['http://', 'https://', 'www.'], '', $domain);

// Parse domain parts
$domain_parts = explode('.', $domain);
if (count($domain_parts) < 2) {
    sendResponse(false, 'Invalid domain format');
}

$extension = '.' . end($domain_parts);
$domain_name = implode('.', array_slice($domain_parts, 0, -1));

try {
    // Check if domain is already in user's cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND full_domain = ?");
    $stmt->execute([$user_id, $domain]);
    
    if ($stmt->fetch()) {
        sendResponse(false, 'This domain is already in your cart');
    }
    
    // Check if user already owns this domain
    $stmt = $pdo->prepare("SELECT id FROM domains WHERE user_id = ? AND full_domain = ?");
    $stmt->execute([$user_id, $domain]);
    
    if ($stmt->fetch()) {
        sendResponse(false, 'You already own this domain');
    }
    
    // Add domain to cart
    $stmt = $pdo->prepare("
        INSERT INTO cart (user_id, domain_name, extension, full_domain, price, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([$user_id, $domain_name, $extension, $domain, $price]);
    
    if ($result) {
        $cart_id = $pdo->lastInsertId();
        
        // Get cart count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetch()['count'];
        
        sendResponse(true, 'Domain added to cart successfully!', [
            'domain' => $domain,
            'price' => $price,
            'cart_id' => $cart_id,
            'cart_count' => $cart_count
        ]);
    } else {
        sendResponse(false, 'Failed to add domain to cart');
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>

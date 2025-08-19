<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();

// Function to log errors
function logError($message, $file = __FILE__, $line = __LINE__) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] Error in $file on line $line: $message" . PHP_EOL;
    error_log($logMessage, 3, 'buy_errors.log');
}

// Function to display detailed error information
function displayError($error, $details = null) {
    $errorHtml = '<div class="alert alert-error">';
    $errorHtml .= '<strong>Error:</strong> ' . htmlspecialchars($error);
    
    if ($details && is_array($details)) {
        $errorHtml .= '<br><br><strong>Debug Information:</strong><br>';
        $errorHtml .= '<pre style="background: #f1f5f9; padding: 10px; border-radius: 5px; font-size: 12px; overflow-x: auto;">';
        $errorHtml .= print_r($details, true);
        $errorHtml .= '</pre>';
    }
    
    $errorHtml .= '</div>';
    return $errorHtml;
}

try {
    require_once 'db.php';
} catch (Exception $e) {
    logError("Database connection failed: " . $e->getMessage());
    die(displayError("Database connection failed. Please try again later.", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_details = [];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payment_method = trim($_POST['payment_method'] ?? '');
        $billing_name = trim($_POST['billing_name'] ?? '');
        $billing_email = trim($_POST['billing_email'] ?? '');
        $billing_address = trim($_POST['billing_address'] ?? '');
        
        // Validate required fields
        $validation_errors = [];
        if (empty($payment_method)) $validation_errors[] = "Payment method is required";
        if (empty($billing_name)) $validation_errors[] = "Full name is required";
        if (empty($billing_email)) $validation_errors[] = "Email address is required";
        if (!filter_var($billing_email, FILTER_VALIDATE_EMAIL)) $validation_errors[] = "Invalid email format";
        
        if (!empty($validation_errors)) {
            throw new Exception("Validation failed: " . implode(", ", $validation_errors));
        }
        
        // Get cart items with error handling
        try {
            $cart_query = "SELECT * FROM cart WHERE user_id = ?";
            $cart_stmt = $pdo->prepare($cart_query);
            
            if (!$cart_stmt) {
                throw new Exception("Failed to prepare cart query: " . implode(" ", $pdo->errorInfo()));
            }
            
            $cart_stmt->execute([$user_id]);
            $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cart_items)) {
                throw new Exception("Your cart is empty. Please add domains before checkout.");
            }
            
        } catch (Exception $e) {
            logError("Cart query error: " . $e->getMessage());
            throw new Exception("Failed to retrieve cart items: " . $e->getMessage());
        }
        
        // Begin transaction with error handling
        try {
            $pdo->beginTransaction();
        } catch (Exception $e) {
            throw new Exception("Failed to start transaction: " . $e->getMessage());
        }
        
        try {
            $total_amount = 0;
            $domains_purchased = [];
            $processed_items = 0;
            
            // Create order first
            $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $order_query = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, payment_status, billing_address) VALUES (?, ?, ?, 'completed', ?, 'paid', ?)";
            
            // Calculate total first
            foreach ($cart_items as $cart_item) {
                $price = floatval($cart_item['price'] ?? 0);
                $total_amount += $price;
            }
            
            $order_stmt = $pdo->prepare($order_query);
            if (!$order_stmt) {
                throw new Exception("Failed to prepare order query: " . implode(" ", $pdo->errorInfo()));
            }
            
            $result = $order_stmt->execute([$user_id, $order_number, $total_amount, $payment_method, $billing_address]);
            if (!$result) {
                throw new Exception("Failed to create order: " . implode(" ", $order_stmt->errorInfo()));
            }
            
            $order_id = $pdo->lastInsertId();
            
            // Process each cart item
            foreach ($cart_items as $cart_item) {
                $full_domain = trim($cart_item['full_domain'] ?? '');
                $price = floatval($cart_item['price'] ?? 0);
                
                if (empty($full_domain)) {
                    throw new Exception("Invalid domain found in cart item ID: " . ($cart_item['id'] ?? 'unknown'));
                }
                
                if ($price <= 0) {
                    throw new Exception("Invalid price for domain: $full_domain");
                }
                
                // Clean the domain first
                $full_domain = ltrim($full_domain, '.');
                
                // Validate domain format
                if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9]*\.[a-zA-Z]{2,}(\.[a-zA-Z]{2,})*$/', $full_domain)) {
                    // If regex fails, try a simpler validation
                    if (!strpos($full_domain, '.') || strlen($full_domain) < 3) {
                        throw new Exception("Invalid domain format: $full_domain");
                    }
                }
                
                // Split domain name and extension more reliably
                $last_dot_pos = strrpos($full_domain, '.');
                if ($last_dot_pos === false) {
                    throw new Exception("Invalid domain format: $full_domain");
                }
                
                // For domains like "example.co.uk", we want to get the main domain part
                $domain_parts = explode('.', $full_domain);
                if (count($domain_parts) < 2) {
                    throw new Exception("Invalid domain format: $full_domain");
                }
                
                $domain_name = $domain_parts[0];
                $extension = '.' . implode('.', array_slice($domain_parts, 1));
                
                // Final validation
                if (empty($domain_name) || strlen($domain_name) < 1) {
                    throw new Exception("Invalid domain name part: $full_domain");
                }
                
                if (empty($extension) || strlen($extension) < 2) {
                    throw new Exception("Invalid domain extension: $full_domain");
                }
                
                $expiry_date = date('Y-m-d', strtotime('+1 year'));
                $domain_query = "INSERT INTO user_domains (user_id, domain_name, extension, full_domain, price, registration_date, expiration_date, years, status, order_id) VALUES (?, ?, ?, ?, ?, CURDATE(), ?, 1, 'active', ?)";
                $domain_stmt = $pdo->prepare($domain_query);
                
                if (!$domain_stmt) {
                    throw new Exception("Failed to prepare domain insert query: " . implode(" ", $pdo->errorInfo()));
                }
                
                $result = $domain_stmt->execute([$user_id, $domain_name, $extension, $full_domain, $price, $expiry_date, $order_id]);
                
                if (!$result) {
                    $error_info = $domain_stmt->errorInfo();
                    throw new Exception("Failed to insert domain '$full_domain': " . implode(" ", $error_info));
                }
                
                // Add to order items
                $order_item_query = "INSERT INTO order_items (order_id, domain_name, extension, full_domain, price, years) VALUES (?, ?, ?, ?, ?, 1)";
                $order_item_stmt = $pdo->prepare($order_item_query);
                
                if ($order_item_stmt) {
                    $order_item_stmt->execute([$order_id, $domain_name, $extension, $full_domain, $price]);
                }
                
                $domains_purchased[] = $full_domain;
                $processed_items++;
                
                logError("Successfully processed domain: $full_domain for user: $user_id");
            }
            
            if ($processed_items === 0) {
                throw new Exception("No items were processed from cart");
            }
            
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $clear_stmt = $pdo->prepare($clear_cart);
            
            if (!$clear_stmt) {
                throw new Exception("Failed to prepare cart clear query: " . implode(" ", $pdo->errorInfo()));
            }
            
            $result = $clear_stmt->execute([$user_id]);
            
            if (!$result) {
                throw new Exception("Failed to clear cart: " . implode(" ", $clear_stmt->errorInfo()));
            }
            
            // Commit transaction
            $pdo->commit();
            
            $success_message = "Payment successful! Your domains have been registered.";
            logError("Transaction completed successfully for user: $user_id, Total: $total_amount, Order: $order_number");
            
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollBack();
            logError("Transaction failed and rolled back: " . $e->getMessage());
            throw new Exception("Payment processing failed: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $error_details = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'post_data' => $_POST,
            'error_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'database_error' => $pdo->errorInfo(),
            'trace' => $e->getTraceAsString()
        ];
        logError("Buy.php error: " . $e->getMessage(), $e->getFile(), $e->getLine());
    }
}

// Get cart items for display with error handling
$cart_items = [];
$total_amount = 0;

try {
    $cart_query = "SELECT * FROM cart WHERE user_id = ?";
    $cart_stmt = $pdo->prepare($cart_query);
    
    if (!$cart_stmt) {
        throw new Exception("Failed to prepare cart display query");
    }
    
    $cart_stmt->execute([$user_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($cart_items as $item) {
        $total_amount += floatval($item['price'] ?? 0);
    }
    
} catch (Exception $e) {
    $error_message = "Failed to load cart items: " . $e->getMessage();
    $error_details = [
        'error' => $e->getMessage(),
        'user_id' => $user_id,
        'query' => $cart_query ?? 'N/A'
    ];
    logError("Cart display error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Purchase - GoDaddy Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .cart-summary {
            background: #f8fafc;
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #e2e8f0;
        }

        .cart-summary h3 {
            color: #1e293b;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .cart-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
            color: #4f46e5;
        }

        .domain-name {
            font-weight: 500;
            color: #374151;
        }

        .price {
            font-weight: 600;
            color: #059669;
        }

        .billing-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            border: 2px solid #e2e8f0;
        }

        .billing-form h3 {
            color: #1e293b;
            margin-bottom: 25px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-option label {
            display: block;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .payment-option input[type="radio"]:checked + label {
            border-color: #4f46e5;
            background: #4f46e5;
            color: white;
        }

        .buy-button {
            width: 100%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .buy-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3);
        }

        .buy-button:active {
            transform: translateY(0);
        }

        .buy-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #3730a3;
        }

        .debug-info {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }

        .error-toggle {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Complete Your Purchase</h1>
            <p>Secure checkout for your domain registration</p>
        </div>

        <div class="content">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                    <?php if (isset($domains_purchased) && !empty($domains_purchased)): ?>
                        <br><br>
                        <strong>Domains purchased:</strong>
                        <ul style="margin-top: 10px;">
                            <?php foreach ($domains_purchased as $domain): ?>
                                <li><?php echo htmlspecialchars($domain); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <a href="dashboard.php" class="back-link">Go to Dashboard</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <?php echo displayError($error_message, $error_details); ?>
            <?php endif; ?>

            <?php if (empty($cart_items) && empty($success_message)): ?>
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <p>Add some domains to your cart before proceeding to checkout.</p>
                    <a href="index.php" class="back-link">← Back to Domain Search</a>
                </div>
            <?php elseif (!empty($cart_items)): ?>
                <form method="POST" action="" id="checkoutForm">
                    <div class="checkout-grid">
                        <div class="cart-summary">
                            <h3>Order Summary</h3>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item">
                                    <span class="domain-name"><?php echo htmlspecialchars($item['full_domain'] ?? 'Unknown Domain'); ?></span>
                                    <span class="price">$<?php echo number_format(floatval($item['price'] ?? 0), 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div class="cart-item">
                                <span><strong>Total Amount:</strong></span>
                                <span class="price"><strong>$<?php echo number_format($total_amount, 2); ?></strong></span>
                            </div>
                        </div>

                        <div class="billing-form">
                            <h3>Billing Information</h3>
                            
                            <div class="form-group">
                                <label for="billing_name">Full Name *</label>
                                <input type="text" id="billing_name" name="billing_name" required 
                                       value="<?php echo htmlspecialchars($_POST['billing_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="billing_email">Email Address *</label>
                                <input type="email" id="billing_email" name="billing_email" required 
                                       value="<?php echo htmlspecialchars($_POST['billing_email'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="billing_address">Billing Address</label>
                                <textarea id="billing_address" name="billing_address" rows="3"><?php echo htmlspecialchars($_POST['billing_address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Payment Method *</label>
                                <div class="payment-methods">
                                    <div class="payment-option">
                                        <input type="radio" id="credit_card" name="payment_method" value="credit_card" required>
                                        <label for="credit_card">Credit Card</label>
                                    </div>
                                    <div class="payment-option">
                                        <input type="radio" id="paypal" name="payment_method" value="paypal" required>
                                        <label for="paypal">PayPal</label>
                                    </div>
                                    <div class="payment-option">
                                        <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" required>
                                        <label for="bank_transfer">Bank Transfer</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="buy-button" id="buyButton">
                        Complete Purchase - $<?php echo number_format($total_amount, 2); ?>
                    </button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="cart.php" class="back-link">← Back to Cart</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add interactive effects and error handling
        document.addEventListener('DOMContentLoaded', function() {
            const buyButton = document.getElementById('buyButton');
            const form = document.getElementById('checkoutForm');
            
            if (form && buyButton) {
                form.addEventListener('submit', function(e) {
                    // Validate form before submission
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = '#ef4444';
                        } else {
                            field.style.borderColor = '#e2e8f0';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields.');
                        return false;
                    }
                    
                    // Show processing state
                    buyButton.innerHTML = 'Processing Payment...';
                    buyButton.disabled = true;
                    buyButton.style.background = '#9ca3af';
                });
            }
            
            // Toggle debug information
            const errorToggles = document.querySelectorAll('.error-toggle');
            errorToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const debugInfo = this.nextElementSibling;
                    if (debugInfo && debugInfo.classList.contains('debug-info')) {
                        debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
                        this.textContent = debugInfo.style.display === 'none' ? 'Show Debug Info' : 'Hide Debug Info';
                    }
                });
            });
        });
    </script>
</body>
</html>

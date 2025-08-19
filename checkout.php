<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get cart items
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_POST && isset($_POST['process_payment'])) {
    $billing_name = $_POST['billing_name'] ?? '';
    $billing_email = $_POST['billing_email'] ?? '';
    $billing_address = $_POST['billing_address'] ?? '';
    $billing_city = $_POST['billing_city'] ?? '';
    $billing_country = $_POST['billing_country'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (!empty($billing_name) && !empty($billing_email) && !empty($payment_method)) {
        // Process payment (mock)
        $order_id = 'ORD' . time() . rand(1000, 9999);
        
        // Move cart items to user_domains
        foreach ($cart_items as $item) {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $stmt = $pdo->prepare("INSERT INTO user_domains (user_id, domain_name, price, registration_date, expiry_date, status) VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'active')");
                $stmt->execute([$user_id, $item['full_domain'], $item['price']]);
            }
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Redirect to success page
        $_SESSION['order_success'] = $order_id;
        header('Location: dashboard.php?success=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GoDaddy Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease-out;
        }

        .header h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }

        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
        }

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .checkout-form {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .order-summary {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        label {
            display: block;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        input, select {
            width: 100%;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .payment-method label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 0;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
        }

        .payment-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .domain-info h4 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .domain-info p {
            color: #718096;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: #667eea;
            font-size: 1.1rem;
        }

        .total-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-row.final {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .checkout-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            position: relative;
            overflow: hidden;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .checkout-btn:active {
            transform: translateY(0);
        }

        .checkout-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .checkout-btn:hover::before {
            left: 100%;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #718096;
            font-size: 0.9rem;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-cart h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #4a5568;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            transform: translateX(-5px);
            color: #764ba2;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .checkout-form, .order-summary {
                padding: 30px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Secure Checkout</h1>
            <p>Complete your domain registration securely</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="checkout-form">
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <p>Add some domains to your cart before checking out.</p>
                    <a href="index.php" class="checkout-btn" style="display: inline-block; text-decoration: none; margin-top: 20px; width: auto; padding: 15px 30px;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="checkout-container">
                <div class="checkout-form">
                    <a href="cart.php" class="back-btn">
                        ← Back to Cart
                    </a>

                    <form method="POST" action="">
                        <div class="section-title">
                            📋 Billing Information
                        </div>

                        <div class="form-group">
                            <label for="billing_name">Full Name *</label>
                            <input type="text" id="billing_name" name="billing_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="billing_email">Email Address *</label>
                            <input type="email" id="billing_email" name="billing_email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="billing_address">Address *</label>
                            <input type="text" id="billing_address" name="billing_address" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="billing_city">City *</label>
                                <input type="text" id="billing_city" name="billing_city" required>
                            </div>
                            <div class="form-group">
                                <label for="billing_country">Country *</label>
                                <select id="billing_country" name="billing_country" required>
                                    <option value="">Select Country</option>
                                    <option value="US">United States</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="CA">Canada</option>
                                    <option value="AU">Australia</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                    <option value="PK">Pakistan</option>
                                    <option value="IN">India</option>
                                </select>
                            </div>
                        </div>

                        <div class="section-title" style="margin-top: 40px;">
                            💳 Payment Method
                        </div>

                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" required>
                                <label for="credit_card">
                                    <div class="payment-icon">💳</div>
                                    Credit Card
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">
                                    <div class="payment-icon">🅿️</div>
                                    PayPal
                                </label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                <label for="bank_transfer">
                                    <div class="payment-icon">🏦</div>
                                    Bank Transfer
                                </label>
                            </div>
                        </div>

                        <button type="submit" name="process_payment" class="checkout-btn pulse">
                            🔒 Complete Purchase - $<?= number_format($total, 2) ?>
                        </button>

                        <div class="security-badge">
                            🔒 Your payment information is secure and encrypted
                        </div>
                    </form>
                </div>

                <div class="order-summary">
                    <div class="section-title">
                        📄 Order Summary
                    </div>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="domain-info">
                                <h4><?= htmlspecialchars($item['full_domain']) ?></h4>
                                <p>Quantity: <?= $item['quantity'] ?> × $<?= number_format($item['price'], 2) ?></p>
                            </div>
                            <div class="item-price">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="total-section">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="total-row final">
                            <span>Total:</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate form elements on focus
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Payment method selection animation
            const paymentMethods = document.querySelectorAll('.payment-method input[type="radio"]');
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    // Remove animation from all labels
                    document.querySelectorAll('.payment-method label').forEach(label => {
                        label.style.animation = '';
                    });
                    
                    // Add animation to selected label
                    if (this.checked) {
                        this.nextElementSibling.style.animation = 'pulse 0.5s ease-out';
                    }
                });
            });

            // Form validation with smooth feedback
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = '#e53e3e';
                            field.style.animation = 'shake 0.5s ease-out';
                            isValid = false;
                        } else {
                            field.style.borderColor = '#48bb78';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        // Show error message
                        const errorMsg = document.createElement('div');
                        errorMsg.textContent = 'Please fill in all required fields';
                        errorMsg.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #fed7d7;
                            color: #c53030;
                            padding: 15px 20px;
                            border-radius: 8px;
                            border-left: 4px solid #e53e3e;
                            z-index: 1000;
                            animation: slideInRight 0.3s ease-out;
                        `;
                        document.body.appendChild(errorMsg);
                        
                        setTimeout(() => {
                            errorMsg.remove();
                        }, 3000);
                    }
                });
            }
        });

        // Add shake animation for validation errors
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

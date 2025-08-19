<?php
session_start();
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get cart items
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['years'];
}

// Handle cart actions
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'remove' && isset($_POST['item_id'])) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['item_id'], $_SESSION['user_id']]);
            header('Location: cart.php');
            exit;
        } elseif ($_POST['action'] === 'update_years' && isset($_POST['item_id']) && isset($_POST['years'])) {
            $years = max(1, min(10, intval($_POST['years'])));
            $stmt = $pdo->prepare("UPDATE cart SET years = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$years, $_POST['item_id'], $_SESSION['user_id']]);
            header('Location: cart.php');
            exit;
        } elseif ($_POST['action'] === 'checkout') {
            // Redirect to checkout
            header('Location: checkout.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - DomainPro</title>
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
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .cart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background: #f8f9fa;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .domain-info {
            flex: 1;
        }

        .domain-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .domain-extension {
            color: #667eea;
            font-weight: 600;
        }

        .domain-price {
            color: #666;
            font-size: 0.9rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0 2rem;
        }

        .quantity-label {
            font-weight: 600;
            color: #333;
        }

        .quantity-select {
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            outline: none;
            font-size: 1rem;
        }

        .quantity-select:focus {
            border-color: #667eea;
        }

        .item-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-right: 1rem;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .cart-summary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: white;
            color: #667eea;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-cart h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        .empty-cart p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .quantity-controls {
                margin: 0;
            }
            
            .summary-row {
                font-size: 0.9rem;
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

        .cart-container, .cart-summary {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <div class="header">
            <h1>Shopping Cart</h1>
            <p>Review your domain selections before checkout</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="cart-container">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Start building your online presence by adding some domains to your cart!</p>
                    <a href="index.php" class="btn btn-primary">Search Domains</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="domain-info">
                            <div class="domain-name">
                                <?php echo htmlspecialchars($item['domain_name']); ?>
                                <span class="domain-extension"><?php echo htmlspecialchars($item['extension']); ?></span>
                            </div>
                            <div class="domain-price">$<?php echo number_format($item['price'], 2); ?> per year</div>
                        </div>
                        
                        <div class="quantity-controls">
                            <span class="quantity-label">Years:</span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="update_years">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <select name="years" class="quantity-select" onchange="this.form.submit()">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $item['years'] == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> year<?php echo $i > 1 ? 's' : ''; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </form>
                        </div>
                        
                        <div class="item-total">
                            $<?php echo number_format($item['price'] * $item['years'], 2); ?>
                        </div>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="remove-btn" onclick="return confirm('Remove this domain from cart?')">
                                Remove
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal (<?php echo count($cart_items); ?> item<?php echo count($cart_items) > 1 ? 's' : ''; ?>):</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Privacy Protection (Free):</span>
                    <span>$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="index.php" class="btn btn-secondary" style="flex: 1;">Continue Shopping</a>
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Proceed to Checkout
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add smooth animations
        document.querySelectorAll('.cart-item').forEach((item, index) => {
            item.style.animationDelay = (index * 0.1) + 's';
            item.style.animation = 'fadeInUp 0.6s ease forwards';
        });

        // Auto-submit form when quantity changes
        document.querySelectorAll('.quantity-select').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</body>
</html>

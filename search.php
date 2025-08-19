<?php
session_start();
require_once 'db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Handle advanced search
$searchResults = [];
$searchPerformed = false;

if ($_POST && isset($_POST['search_query'])) {
    $searchQuery = trim($_POST['search_query']);
    $searchPerformed = true;
    
    // Mock advanced search results with various extensions
    $extensions = ['.com', '.net', '.org', '.info', '.biz', '.co', '.io', '.ai', '.tech', '.online'];
    $prices = [9.99, 12.99, 14.99, 19.99, 24.99, 29.99];
    
    foreach ($extensions as $ext) {
        $domain = $searchQuery . $ext;
        $available = rand(0, 1); // Random availability
        $price = $prices[array_rand($prices)];
        
        $searchResults[] = [
            'domain' => $domain,
            'available' => $available,
            'price' => $price,
            'premium' => $price > 20
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Domain Search - GoDaddy Clone</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        .search-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .search-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .filter-group {
            background: rgba(102, 126, 234, 0.1);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }

        .filter-group h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-tag {
            padding: 8px 15px;
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .filter-tag:hover, .filter-tag.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .results-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            font-size: 1.2rem;
            color: #667eea;
            font-weight: 600;
        }

        .sort-options {
            display: flex;
            gap: 10px;
        }

        .sort-btn {
            padding: 8px 15px;
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .sort-btn:hover, .sort-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .domain-grid {
            display: grid;
            gap: 20px;
        }

        .domain-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .domain-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .domain-info {
            flex: 1;
            min-width: 200px;
        }

        .domain-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .domain-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .available {
            background: #d4edda;
            color: #155724;
        }

        .taken {
            background: #f8d7da;
            color: #721c24;
        }

        .premium-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .domain-price {
            text-align: right;
            min-width: 120px;
        }

        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }

        .price-period {
            color: #666;
            font-size: 0.9rem;
        }

        .domain-actions {
            display: flex;
            gap: 10px;
            min-width: 200px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input {
                min-width: auto;
            }

            .domain-card {
                flex-direction: column;
                text-align: center;
            }

            .domain-actions {
                justify-content: center;
                min-width: auto;
            }

            .filters {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            ← Back to Home
        </a>

        <div class="header">
            <h1>Advanced Domain Search</h1>
            <p>Find the perfect domain for your business with our advanced search tools</p>
        </div>

        <div class="search-section">
            <form method="POST" class="search-form">
                <input type="text" name="search_query" class="search-input" 
                       placeholder="Enter your domain idea..." 
                       value="<?php echo htmlspecialchars($_POST['search_query'] ?? ''); ?>" required>
                <button type="submit" class="search-btn">Search Domains</button>
            </form>

            <div class="filters">
                <div class="filter-group">
                    <h3>Domain Extensions</h3>
                    <div class="filter-options">
                        <div class="filter-tag active">.com</div>
                        <div class="filter-tag">.net</div>
                        <div class="filter-tag">.org</div>
                        <div class="filter-tag">.io</div>
                        <div class="filter-tag">.ai</div>
                        <div class="filter-tag">.tech</div>
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Price Range</h3>
                    <div class="filter-options">
                        <div class="filter-tag active">All Prices</div>
                        <div class="filter-tag">Under $15</div>
                        <div class="filter-tag">$15 - $25</div>
                        <div class="filter-tag">$25+</div>
                    </div>
                </div>

                <div class="filter-group">
                    <h3>Availability</h3>
                    <div class="filter-options">
                        <div class="filter-tag active">All</div>
                        <div class="filter-tag">Available</div>
                        <div class="filter-tag">Premium</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($searchPerformed): ?>
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    Found <?php echo count($searchResults); ?> results for "<?php echo htmlspecialchars($_POST['search_query']); ?>"
                </div>
                <div class="sort-options">
                    <div class="sort-btn active">Price: Low to High</div>
                    <div class="sort-btn">Price: High to Low</div>
                    <div class="sort-btn">Alphabetical</div>
                </div>
            </div>

            <?php if (!empty($searchResults)): ?>
            <div class="domain-grid">
                <?php foreach ($searchResults as $result): ?>
                <div class="domain-card">
                    <div class="domain-info">
                        <div class="domain-name"><?php echo htmlspecialchars($result['domain']); ?></div>
                        <div class="domain-status">
                            <span class="status-badge <?php echo $result['available'] ? 'available' : 'taken'; ?>">
                                <?php echo $result['available'] ? 'Available' : 'Taken'; ?>
                            </span>
                            <?php if ($result['premium']): ?>
                            <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($result['available']): ?>
                    <div class="domain-price">
                        <div class="price">$<?php echo number_format($result['price'], 2); ?></div>
                        <div class="price-period">per year</div>
                    </div>

                    <div class="domain-actions">
                        <button class="btn btn-secondary" onclick="addToCart('<?php echo $result['domain']; ?>', <?php echo $result['price']; ?>)">
                            Add to Cart
                        </button>
                        <a href="buy.php?domain=<?php echo urlencode($result['domain']); ?>&price=<?php echo $result['price']; ?>" class="btn btn-primary">
                            Buy Now
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="domain-actions">
                        <button class="btn btn-secondary" disabled>
                            Not Available
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <h3>No domains found</h3>
                <p>Try searching with different keywords or check your spelling.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                // Remove active class from siblings
                this.parentNode.querySelectorAll('.filter-tag').forEach(sibling => {
                    sibling.classList.remove('active');
                });
                // Add active class to clicked tag
                this.classList.add('active');
            });
        });

        // Sort functionality
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.sort-btn').forEach(sibling => {
                    sibling.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Add to cart functionality
        function addToCart(domain, price) {
            if (!<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                alert('Please login to add domains to cart');
                window.location.href = 'login.php';
                return;
            }

            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Adding...';
            button.disabled = true;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    domain: domain,
                    price: price,
                    action: 'add_to_cart'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = 'Added!';
                    button.style.background = '#28a745';
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.disabled = false;
                        button.style.background = '';
                    }, 2000);
                } else {
                    alert('Error: ' + data.message);
                    button.textContent = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                button.textContent = originalText;
                button.disabled = false;
            });
        }
    </script>
</body>
</html>

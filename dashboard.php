<?php
session_start();
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUserData($_SESSION['user_id']);

// Get user's purchased domains
$stmt = $pdo->prepare("SELECT * FROM user_domains WHERE user_id = ? ORDER BY registration_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$domains = $stmt->fetchAll();

// Get cart items count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cart_count = $stmt->fetch()['count'];

// Get total domains count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_domains WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_domains = $stmt->fetch()['count'];

// Get active domains count
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_domains WHERE user_id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$active_domains = $stmt->fetch()['count'];

// Get expiring domains count (within 30 days)
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_domains WHERE user_id = ? AND expiration_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$expiring_domains = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DomainPro</title>
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
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Dashboard Content */
        .dashboard {
            padding: 2rem 0;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Domains Section */
        .domains-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            color: #333;
            font-size: 1.8rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .domains-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .domains-table th,
        .domains-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .domains-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .domain-name {
            font-weight: 600;
            color: #333;
        }

        .domain-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .domain-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            border-radius: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Quick Actions */
        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .action-desc {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .domains-table {
                font-size: 0.9rem;
            }
            
            .domains-table th,
            .domains-table td {
                padding: 0.75rem 0.5rem;
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

        .dashboard > * {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">DomainPro</div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="search.php">Search Domains</a>
                <a href="cart.php">Cart (<?php echo $cart_count; ?>)</a>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
                <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</span>
                <a href="logout.php" class="btn btn-secondary btn-small">Logout</a>
            </div>
        </nav>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <p>Manage your domains, view your account, and explore new opportunities.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🌐</div>
                    <div class="stat-number"><?php echo $total_domains; ?></div>
                    <div class="stat-label">Total Domains</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $active_domains; ?></div>
                    <div class="stat-label">Active Domains</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-number"><?php echo $cart_count; ?></div>
                    <div class="stat-label">Items in Cart</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-number"><?php echo $expiring_domains; ?></div>
                    <div class="stat-label">Expiring Soon</div>
                </div>
            </div>

            <div class="domains-section">
                <div class="section-header">
                    <h2>Your Domains</h2>
                    <a href="search.php" class="btn">Register New Domain</a>
                </div>

                <?php if (empty($domains)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🌐</div>
                        <h3>No domains registered yet</h3>
                        <p>Start building your online presence by registering your first domain!</p>
                        <a href="search.php" class="btn" style="margin-top: 1rem;">Search Domains</a>
                    </div>
                <?php else: ?>
                    <table class="domains-table">
                        <thead>
                            <tr>
                                <th>Domain Name</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>Expiration Date</th>
                                <th>Auto Renew</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domains as $domain): ?>
                                <tr>
                                    <td>
                                        <div class="domain-name"><?php echo htmlspecialchars($domain['full_domain']); ?></div>
                                    </td>
                                    <td>
                                        <span class="domain-status status-<?php echo $domain['status']; ?>">
                                            <?php echo ucfirst($domain['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($domain['registration_date'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($domain['expiration_date'])); ?></td>
                                    <td><?php echo $domain['auto_renew'] ? '✅ Yes' : '❌ No'; ?></td>
                                    <td>
                                        <div class="domain-actions">
                                            <button class="btn btn-small" onclick="manageDomain(<?php echo $domain['id']; ?>)">Manage</button>
                                            <button class="btn btn-secondary btn-small" onclick="renewDomain(<?php echo $domain['id']; ?>)">Renew</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <a href="search.php" class="action-card">
                        <div class="action-icon">🔍</div>
                        <div class="action-title">Search Domains</div>
                        <div class="action-desc">Find your perfect domain name</div>
                    </a>
                    <a href="cart.php" class="action-card">
                        <div class="action-icon">🛒</div>
                        <div class="action-title">View Cart</div>
                        <div class="action-desc">Complete your purchases</div>
                    </a>
                    <a href="profile.php" class="action-card">
                        <div class="action-icon">👤</div>
                        <div class="action-title">Edit Profile</div>
                        <div class="action-desc">Update your account info</div>
                    </a>
                    <a href="support.php" class="action-card">
                        <div class="action-icon">💬</div>
                        <div class="action-title">Get Support</div>
                        <div class="action-desc">Contact our help team</div>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function manageDomain(domainId) {
            // Redirect to domain management page
            window.location.href = 'manage-domain.php?id=' + domainId;
        }

        function renewDomain(domainId) {
            if (confirm('Are you sure you want to renew this domain for another year?')) {
                // Add to cart for renewal
                fetch('add_renewal_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        domain_id: domainId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Domain renewal added to cart!');
                        window.location.href = 'cart.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error processing renewal');
                });
            }
        }

        // Add some interactive effects
        document.querySelectorAll('.stat-card, .action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>

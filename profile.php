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

// Get user's domains
$stmt = $pdo->prepare("SELECT * FROM user_domains WHERE user_id = ? ORDER BY purchase_date DESC");
$stmt->execute([$user_id]);
$domains = $stmt->fetchAll();

// Handle profile update
$updateMessage = '';
if ($_POST && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $company = trim($_POST['company']);
    
    if (!empty($name) && !empty($email)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, company = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $company, $user_id])) {
            $updateMessage = 'Profile updated successfully!';
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $updateMessage = 'Error updating profile. Please try again.';
        }
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $updateMessage = 'Password changed successfully!';
            } else {
                $updateMessage = 'Error changing password. Please try again.';
            }
        } else {
            $updateMessage = 'New passwords do not match or are too short.';
        }
    } else {
        $updateMessage = 'Current password is incorrect.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GoDaddy Clone</title>
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

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
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
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
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

        .domains-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .domains-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .domains-grid {
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

        .domain-details {
            color: #666;
            font-size: 0.9rem;
        }

        .domain-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .active {
            background: #d4edda;
            color: #155724;
        }

        .expiring {
            background: #fff3cd;
            color: #856404;
        }

        .domain-actions {
            display: flex;
            gap: 10px;
            min-width: 200px;
            justify-content: flex-end;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .domain-card {
                flex-direction: column;
                text-align: center;
            }

            .domain-actions {
                justify-content: center;
                min-width: auto;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">
            ← Back to Dashboard
        </a>

        <div class="header">
            <h1>My Profile</h1>
            <p>Manage your account information and domain portfolio</p>
        </div>

        <?php if ($updateMessage): ?>
        <div class="alert <?php echo strpos($updateMessage, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($updateMessage); ?>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($domains); ?></div>
                <div class="stat-label">Total Domains</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($domains, function($d) { return strtotime($d['expiry_date']) > time(); })); ?></div>
                <div class="stat-label">Active Domains</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($domains, function($d) { return strtotime($d['expiry_date']) < strtotime('+30 days'); })); ?></div>
                <div class="stat-label">Expiring Soon</div>
            </div>
        </div>

        <div class="profile-grid">
            <div class="profile-card">
                <div class="card-header">
                    <div class="card-icon">👤</div>
                    <div class="card-title">Personal Information</div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-input" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" class="form-input" 
                               value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        Update Profile
                    </button>
                </form>
            </div>

            <div class="profile-card">
                <div class="card-header">
                    <div class="card-icon">🔒</div>
                    <div class="card-title">Change Password</div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary">
                        Change Password
                    </button>
                </form>
            </div>
        </div>

        <div class="domains-section">
            <div class="domains-header">
                <div class="card-icon">🌐</div>
                <div class="card-title">My Domains (<?php echo count($domains); ?>)</div>
            </div>

            <?php if (!empty($domains)): ?>
            <div class="domains-grid">
                <?php foreach ($domains as $domain): ?>
                <div class="domain-card">
                    <div class="domain-info">
                        <div class="domain-name"><?php echo htmlspecialchars($domain['domain_name']); ?></div>
                        <div class="domain-details">
                            Purchased: <?php echo date('M j, Y', strtotime($domain['purchase_date'])); ?><br>
                            Expires: <?php echo date('M j, Y', strtotime($domain['expiry_date'])); ?>
                        </div>
                        <div class="domain-status">
                            <?php 
                            $daysUntilExpiry = (strtotime($domain['expiry_date']) - time()) / (60 * 60 * 24);
                            if ($daysUntilExpiry > 30): ?>
                                <span class="status-badge active">Active</span>
                            <?php else: ?>
                                <span class="status-badge expiring">Expiring Soon</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="domain-actions">
                        <button class="btn btn-secondary">Renew</button>
                        <button class="btn btn-secondary">Manage</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No domains yet</h3>
                <p>Start building your online presence by registering your first domain.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Search Domains</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

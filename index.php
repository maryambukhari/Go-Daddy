<?php
session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DomainPro - Your Domain Registration Partner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
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
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #667eea;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(45deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            padding: 150px 0 100px;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        /* Domain Search */
        .domain-search {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            max-width: 800px;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 50px;
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s ease;
            min-width: 300px;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-btn {
            padding: 1rem 2rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .extensions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .extension-tag {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .extension-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Pricing Section */
        .pricing {
            padding: 100px 0;
            text-align: center;
            color: white;
        }

        .pricing h2 {
            font-size: 2.5rem;
            margin-bottom: 3rem;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .pricing-card {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .pricing-card.popular {
            border: 3px solid #667eea;
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .price {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin: 1rem 0;
        }

        .price span {
            font-size: 1rem;
            color: #666;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 3rem 0 1rem;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #667eea;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #667eea;
        }

        /* Animations */
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

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
                min-width: auto;
            }
            
            .domain-search {
                padding: 2rem;
                margin: 0 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            margin: 2rem 0;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Results Section */
        .results {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .result-item:hover {
            background: #f8f9fa;
        }

        .domain-info {
            flex: 1;
        }

        .domain-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .domain-status {
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .available {
            color: #28a745;
        }

        .unavailable {
            color: #dc3545;
        }

        .domain-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: #667eea;
            margin-right: 1rem;
        }

        .add-to-cart {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 120px;
            text-align: center;
        }

        .add-to-cart:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .add-to-cart:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .add-to-cart:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">DomainPro</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#domains">Domains</a></li>
                <li><a href="#hosting">Hosting</a></li>
                <li><a href="#support">Support</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Find Your Perfect Domain</h1>
                <p>Search millions of domains and get online today with our award-winning domain registration service</p>
                
                <div class="domain-search">
                    <form class="search-form" id="domainSearchForm">
                        <input type="text" class="search-input" id="domainInput" placeholder="Enter your domain name..." required>
                        <button type="submit" class="search-btn">Search Domain</button>
                    </form>
                    
                    <div class="extensions">
                        <span class="extension-tag" onclick="addExtension('.com')">.com - $12.99</span>
                        <span class="extension-tag" onclick="addExtension('.net')">.net - $14.99</span>
                        <span class="extension-tag" onclick="addExtension('.org')">.org - $13.99</span>
                        <span class="extension-tag" onclick="addExtension('.info')">.info - $9.99</span>
                        <span class="extension-tag" onclick="addExtension('.biz')">.biz - $11.99</span>
                    </div>

                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <p>Searching domains...</p>
                    </div>

                    <div class="results" id="results"></div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2 style="text-align: center; color: white; font-size: 2.5rem; margin-bottom: 1rem;">Why Choose DomainPro?</h2>
                <p style="text-align: center; color: rgba(255,255,255,0.9); font-size: 1.2rem;">We provide the best domain registration experience with unmatched features</p>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🚀</div>
                        <h3>Lightning Fast</h3>
                        <p>Get your domain registered instantly with our automated system. No waiting, no delays.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure & Private</h3>
                        <p>Free privacy protection included with every domain. Keep your personal information safe.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💰</div>
                        <h3>Best Prices</h3>
                        <p>Competitive pricing with no hidden fees. What you see is what you pay.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Easy Management</h3>
                        <p>Intuitive dashboard to manage all your domains in one place. Simple and powerful.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📞</div>
                        <h3>24/7 Support</h3>
                        <p>Expert support team available round the clock to help you with any questions.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🌍</div>
                        <h3>Global Reach</h3>
                        <p>Access to hundreds of domain extensions from around the world.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pricing">
            <div class="container">
                <h2>Domain Pricing</h2>
                <p style="font-size: 1.2rem; opacity: 0.9;">Choose from our competitive domain pricing plans</p>
                
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <h3>.com</h3>
                        <div class="price">$12.99<span>/year</span></div>
                        <p>Most popular choice for businesses and personal websites</p>
                        <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Register Now</a>
                    </div>
                    <div class="pricing-card popular">
                        <h3>.net</h3>
                        <div class="price">$14.99<span>/year</span></div>
                        <p>Perfect for tech companies and network services</p>
                        <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Register Now</a>
                    </div>
                    <div class="pricing-card">
                        <h3>.org</h3>
                        <div class="price">$13.99<span>/year</span></div>
                        <p>Ideal for organizations and non-profits</p>
                        <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Register Now</a>
                    </div>
                    <div class="pricing-card">
                        <h3>.info</h3>
                        <div class="price">$9.99<span>/year</span></div>
                        <p>Great for informational websites and blogs</p>
                        <a href="#" class="btn btn-primary" style="margin-top: 1rem;">Register Now</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Products</h3>
                    <a href="#">Domain Registration</a>
                    <a href="#">Web Hosting</a>
                    <a href="#">SSL Certificates</a>
                    <a href="#">Email Hosting</a>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <a href="#">Help Center</a>
                    <a href="#">Contact Us</a>
                    <a href="#">Live Chat</a>
                    <a href="#">System Status</a>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <a href="#">About Us</a>
                    <a href="#">Careers</a>
                    <a href="#">Press</a>
                    <a href="#">Partners</a>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                    <a href="#">GDPR</a>
                </div>
            </div>
            <hr style="border: 1px solid #444; margin: 2rem 0;">
            <p>&copy; 2024 DomainPro. All rights reserved. | Made with ❤️ for domain enthusiasts</p>
        </div>
    </footer>

    <script>
        // Domain search functionality
        document.getElementById('domainSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            searchDomains();
        });

        function addExtension(ext) {
            const input = document.getElementById('domainInput');
            const currentValue = input.value.replace(/\.(com|net|org|info|biz|co)$/i, '');
            input.value = currentValue + ext;
            searchDomains();
        }

        function searchDomains() {
            const domainInput = document.getElementById('domainInput').value.trim();
            if (!domainInput) return;

            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            
            loading.style.display = 'block';
            results.style.display = 'none';

            // Simulate API call delay
            setTimeout(() => {
                loading.style.display = 'none';
                results.style.display = 'block';
                displayResults(domainInput);
            }, 1500);
        }

        function displayResults(domain) {
            const extensions = ['.com', '.net', '.org', '.info', '.biz', '.co'];
            const prices = {'.com': 12.99, '.net': 14.99, '.org': 13.99, '.info': 9.99, '.biz': 11.99, '.co': 24.99};
            const unavailableDomains = ['google', 'facebook', 'amazon', 'microsoft', 'apple'];
            
            const baseDomain = domain.replace(/\.(com|net|org|info|biz|co)$/i, '');
            const results = document.getElementById('results');
            
            let html = '<h3 style="margin-bottom: 1rem; color: #333;">Search Results for "' + baseDomain + '"</h3>';
            
            extensions.forEach(ext => {
                const fullDomain = baseDomain + ext;
                const isAvailable = !unavailableDomains.some(unavailable => 
                    baseDomain.toLowerCase().includes(unavailable)
                );
                const price = prices[ext];
                
                html += `
                    <div class="result-item">
                        <div class="domain-info">
                            <div class="domain-name">${fullDomain}</div>
                            <div class="domain-status ${isAvailable ? 'available' : 'unavailable'}">
                                ${isAvailable ? '✓ Available' : '✗ Unavailable'}
                            </div>
                        </div>
                        <div class="domain-price">$${price}/year</div>
                        <button type="button" class="add-to-cart" ${!isAvailable ? 'disabled' : ''} 
                                data-domain="${fullDomain}" data-price="${price}">
                            ${isAvailable ? 'Add to Cart' : 'Unavailable'}
                        </button>
                    </div>
                `;
            });
            
            results.innerHTML = html;
        }

        function addToCart(domain, price, buttonElement) {
            // Check if user is logged in
            <?php if (!isLoggedIn()): ?>
                if (confirm('You need to login to add domains to cart. Would you like to login now?')) {
                    window.location.href = 'login.php';
                }
                return false;
            <?php endif; ?>

            // Prevent any further event propagation
            if (window.event) {
                window.event.preventDefault();
                window.event.stopPropagation();
                window.event.stopImmediatePropagation();
            }

            // Show loading state
            const button = buttonElement;
            if (!button || button.disabled) return false;
            
            const originalText = button.textContent;
            button.textContent = 'Adding...';
            button.disabled = true;
            button.style.pointerEvents = 'none';

            const requestData = {
                domain: domain,
                price: price,
                action: 'add_to_cart'
            };

            console.log('Sending POST request to add_to_cart.php:', requestData);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log('XHR Response Status:', xhr.status);
                    console.log('XHR Response Text:', xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('Parsed Response:', data);
                            
                            if (data.success) {
                                // Success feedback
                                button.textContent = '✅ Added!';
                                button.style.background = '#28a745';
                                button.style.color = 'white';
                                
                                // Show success message
                                showNotification('Domain added to cart successfully!', 'success');
                                
                                // Redirect to cart after 2 seconds
                                setTimeout(() => {
                                    window.location.href = 'cart.php';
                                }, 2000);
                            } else {
                                // Error feedback
                                showNotification('Error: ' + data.message, 'error');
                                button.textContent = originalText;
                                button.disabled = false;
                                button.style.pointerEvents = 'auto';
                                
                                // Show debug info if available
                                if (data.debug) {
                                    console.error('Debug info:', data.debug);
                                }
                            }
                        } catch (e) {
                            console.error('JSON Parse Error:', e);
                            console.error('Response was:', xhr.responseText);
                            showNotification('Invalid response from server', 'error');
                            button.textContent = originalText;
                            button.disabled = false;
                            button.style.pointerEvents = 'auto';
                        }
                    } else {
                        console.error('HTTP Error:', xhr.status, xhr.statusText);
                        showNotification('Server error occurred. Please try again.', 'error');
                        button.textContent = originalText;
                        button.disabled = false;
                        button.style.pointerEvents = 'auto';
                    }
                }
            };
            
            xhr.onerror = function() {
                console.error('Network Error occurred');
                showNotification('Network error occurred. Please try again.', 'error');
                button.textContent = originalText;
                button.disabled = false;
                button.style.pointerEvents = 'auto';
            };
            
            xhr.send(JSON.stringify(requestData));
            
            return false;
        }

        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(n => n.remove());
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#667eea'};
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 10px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                    z-index: 10000;
                    font-weight: 600;
                    animation: slideInRight 0.3s ease;
                    max-width: 300px;
                    word-wrap: break-word;
                ">
                    ${message}
                    <button onclick="this.parentElement.parentElement.remove()" style="
                        background: none;
                        border: none;
                        color: white;
                        float: right;
                        font-size: 1.2rem;
                        cursor: pointer;
                        margin-left: 10px;
                        padding: 0;
                        line-height: 1;
                    ">&times;</button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 30px rgba(0, 0, 0, 0.15)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            }
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
                }
            });
        }, observerOptions);

        // Observe all feature cards and pricing cards
        document.querySelectorAll('.feature-card, .pricing-card').forEach(card => {
            observer.observe(card);
        });

        document.addEventListener('click', function(e) {
            // Check if clicked element is an add to cart button
            if (e.target && e.target.classList.contains('add-to-cart') && !e.target.disabled) {
                e.preventDefault();
                e.stopPropagation();
                
                const domain = e.target.getAttribute('data-domain');
                const price = e.target.getAttribute('data-price');
                
                if (domain && price) {
                    console.log('Add to cart clicked for:', domain, 'Price:', price);
                    addToCart(domain, price, e.target);
                }
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target && e.target.classList.contains('add-to-cart') && !e.target.disabled) {
                e.preventDefault();
                e.stopPropagation();
                
                const domain = e.target.getAttribute('data-domain');
                const price = e.target.getAttribute('data-price');
                
                if (domain && price) {
                    addToCart(domain, price, e.target);
                }
            }
        });
    </script>
</body>
</html>

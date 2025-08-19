<?php
session_start();
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Handle support ticket submission
$ticketMessage = '';
if ($_POST && isset($_POST['submit_ticket'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $priority = $_POST['priority'];
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // In a real application, you would save this to a support tickets table
        $ticketMessage = 'Your support ticket has been submitted successfully! We will get back to you within 24 hours.';
    } else {
        $ticketMessage = 'Please fill in all required fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center - GoDaddy Clone</title>
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

        .support-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .support-card {
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

        .faq-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .faq-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            color: #667eea;
        }

        .faq-answer {
            margin-top: 15px;
            color: #666;
            line-height: 1.6;
            display: none;
        }

        .faq-answer.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contact-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 15px;
        }

        .contact-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .contact-info {
            color: #666;
            margin-bottom: 15px;
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

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
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

        .priority-high {
            background: #f8d7da;
            color: #721c24;
        }

        .priority-medium {
            background: #fff3cd;
            color: #856404;
        }

        .priority-low {
            background: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .support-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .contact-options {
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
            <h1>Support Center</h1>
            <p>We're here to help you with all your domain and hosting needs</p>
        </div>

        <?php if ($ticketMessage): ?>
        <div class="alert <?php echo strpos($ticketMessage, 'successfully') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo htmlspecialchars($ticketMessage); ?>
        </div>
        <?php endif; ?>

        <div class="contact-options">
            <div class="contact-card">
                <div class="contact-icon">📞</div>
                <div class="contact-title">Phone Support</div>
                <div class="contact-info">Available 24/7</div>
                <div class="contact-info"><strong>1-800-DOMAINS</strong></div>
                <button class="btn btn-secondary">Call Now</button>
            </div>

            <div class="contact-card">
                <div class="contact-icon">💬</div>
                <div class="contact-title">Live Chat</div>
                <div class="contact-info">Average response: 2 minutes</div>
                <div class="contact-info">Available 24/7</div>
                <button class="btn btn-secondary" onclick="startChat()">Start Chat</button>
            </div>

            <div class="contact-card">
                <div class="contact-icon">📧</div>
                <div class="contact-title">Email Support</div>
                <div class="contact-info">Response within 24 hours</div>
                <div class="contact-info">support@godaddyclone.com</div>
                <button class="btn btn-secondary">Send Email</button>
            </div>
        </div>

        <div class="support-grid">
            <div class="support-card">
                <div class="card-header">
                    <div class="card-icon">🎫</div>
                    <div class="card-title">Submit Support Ticket</div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-input" 
                               value="<?php echo $isLoggedIn ? htmlspecialchars($_SESSION['user_name'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo $isLoggedIn ? htmlspecialchars($_SESSION['user_email'] ?? '') : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Priority Level</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low - General inquiry</option>
                            <option value="medium" selected>Medium - Account issue</option>
                            <option value="high">High - Domain problem</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-input" 
                               placeholder="Brief description of your issue" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-textarea" 
                                  placeholder="Please provide detailed information about your issue..." required></textarea>
                    </div>

                    <button type="submit" name="submit_ticket" class="btn btn-primary">
                        Submit Ticket
                    </button>
                </form>
            </div>

            <div class="support-card">
                <div class="card-header">
                    <div class="card-icon">📚</div>
                    <div class="card-title">Knowledge Base</div>
                </div>

                <div style="display: grid; gap: 15px;">
                    <a href="#" class="btn btn-secondary">Domain Registration Guide</a>
                    <a href="#" class="btn btn-secondary">DNS Management</a>
                    <a href="#" class="btn btn-secondary">Domain Transfer Process</a>
                    <a href="#" class="btn btn-secondary">Billing & Payments</a>
                    <a href="#" class="btn btn-secondary">Account Security</a>
                    <a href="#" class="btn btn-secondary">Technical Support</a>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #f0f0f0;">
                    <h4 style="margin-bottom: 15px; color: #333;">Quick Links</h4>
                    <div style="display: grid; gap: 10px;">
                        <a href="#" style="color: #667eea; text-decoration: none;">• Check Domain Status</a>
                        <a href="#" style="color: #667eea; text-decoration: none;">• Renew Domains</a>
                        <a href="#" style="color: #667eea; text-decoration: none;">• Update Contact Info</a>
                        <a href="#" style="color: #667eea; text-decoration: none;">• Manage DNS Records</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-section">
            <div class="card-header">
                <div class="card-icon">❓</div>
                <div class="card-title">Frequently Asked Questions</div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How do I register a new domain?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    To register a new domain, use our search tool on the homepage to check availability. Once you find an available domain, add it to your cart and complete the checkout process. You'll need to provide contact information and payment details.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How long does domain registration take?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Domain registration is typically instant for most extensions. However, some premium domains or specific extensions may take up to 24-48 hours to fully propagate across the internet.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Can I transfer my domain from another registrar?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    Yes! You can transfer your domain to us. The process typically takes 5-7 days and requires an authorization code from your current registrar. Contact our support team for assistance with the transfer process.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    What happens if I don't renew my domain?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    If you don't renew your domain before it expires, it will go through a grace period (usually 30 days) where you can still renew it. After that, it enters a redemption period with higher fees, and eventually becomes available for public registration again.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How do I update my domain's DNS settings?
                    <span>+</span>
                </div>
                <div class="faq-answer">
                    You can manage your DNS settings through your account dashboard. Navigate to "My Domains," select the domain you want to modify, and click on "DNS Management." From there, you can add, edit, or delete DNS records.
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('span');
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-answer').forEach(item => {
                if (item !== answer) {
                    item.classList.remove('active');
                    item.previousElementSibling.querySelector('span').textContent = '+';
                }
            });
            
            // Toggle current FAQ item
            if (answer.classList.contains('active')) {
                answer.classList.remove('active');
                icon.textContent = '+';
            } else {
                answer.classList.add('active');
                icon.textContent = '−';
            }
        }

        function startChat() {
            alert('Live chat feature would be integrated with a chat service like Intercom or Zendesk in a real application.');
        }

        // Auto-expand first FAQ item
        document.addEventListener('DOMContentLoaded', function() {
            const firstFaq = document.querySelector('.faq-question');
            if (firstFaq) {
                toggleFaq(firstFaq);
            }
        });
    </script>
</body>
</html>

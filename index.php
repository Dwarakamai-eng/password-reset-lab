<?php
session_start();
$FLAG = "THM{P455W0RD_R353T_3XPL01T3D}";

// Initialize session data
if (!isset($_SESSION['tokens'])) {
    $_SESSION['tokens'] = [];
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

if (!isset($_SESSION['original_passwords'])) {
    $_SESSION['original_passwords'] = [];
}

// Initialize default victim account
if (!isset($_SESSION['users']['victim@example.com'])) {
    $_SESSION['users']['victim@example.com'] = 'original123';
    $_SESSION['original_passwords']['victim@example.com'] = 'original123';
}

// Handle login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (isset($_SESSION['users'][$email]) && $_SESSION['users'][$email] === $password) {
        $_SESSION['logged_in_user'] = $email;
        
        // Check if password was changed via attack (only show flag if compromised)
        if (isset($_SESSION['original_passwords'][$email]) && $password !== $_SESSION['original_passwords'][$email]) {
            echo "<div style='border:3px solid green;padding:25px;margin:20px;background:#e8f5e8;text-align:center;border-radius:10px;'>";
            echo "<h1>🎉 ATTACK SUCCESSFUL!</h1>";
            echo "<h2 style='color:red;'>FLAG: $FLAG</h2>";
            echo "<p><strong>Congratulations!</strong> You've successfully compromised the account via Host Header Injection!</p>";
            echo "<p>Compromised account: <strong>$email</strong></p>";
            echo "<p>Attacker's password: <strong>$password</strong></p>";
            echo "<p style='color:red;'><strong>⚠️ This account was compromised via password reset attack!</strong></p>";
            echo "</div>";
        } else {
            $login_success = "✅ Login successful! Welcome back, " . htmlspecialchars($email);
        }
    } else {
        $login_error = "❌ Invalid email or password!";
    }
}

// Handle password reset request
if (isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);
    
    if (isset($_SESSION['users'][$email])) {
        $token = bin2hex(random_bytes(16));
        $_SESSION['tokens'][$token] = [
            'email' => $email,
            'expires' => time() + 3600
        ];
        
        // VULNERABLE: Using Host header directly
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
        $reset_link = "http://$host/?token=$token";
        
        echo "<div style='border:3px solid orange;padding:20px;margin:15px;background:#fff3cd;border-radius:8px;'>";
        echo "<h3>📧 Password Reset Link Generated!</h3>";
        echo "<p><strong>Reset Link:</strong></p>";
        echo "<p style='background:#f8f8f8;padding:10px;border:1px solid #ddd;word-break:break-all;border-radius:5px;'>";
        echo "<a href='$reset_link' target='_blank'>$reset_link</a>";
        echo "</p>";
        echo "<p style='color:red;font-weight:bold;'>⚠️ VULNERABILITY: Host = <code>$host</code></p>";
        echo "<p><small>In a real scenario, this link would be sent to the user's email.</small></p>";
        echo "</div>";
    } else {
        $reset_error = "❌ Email not found.";
    }
}

// Handle password reset
if (isset($_GET['token']) && isset($_POST['new_password'])) {
    $token = $_GET['token'];
    
    if (isset($_SESSION['tokens'][$token])) {
        $token_data = $_SESSION['tokens'][$token];
        
        if ($token_data['expires'] < time()) {
            $reset_error = "❌ Token has expired!";
        } else {
            $email = $token_data['email'];
            $_SESSION['users'][$email] = $_POST['new_password'];
            unset($_SESSION['tokens'][$token]);
            
            echo "<div style='border:3px solid green;padding:20px;margin:15px;background:#e8f5e8;border-radius:8px;'>";
            echo "<h2>✅ PASSWORD RESET SUCCESSFUL!</h2>";
            echo "<p>Password has been changed for: <strong>$email</strong></p>";
            echo "<p>New password: <strong>" . $_POST['new_password'] . "</strong></p>";
            echo "<p style='background:#d4edda;padding:15px;border:1px solid #c3e6cb;border-radius:5px;'>";
            echo "<strong>🎯 Attack Complete!</strong> Now try logging in with the new password to get the FLAG!";
            echo "</p>";
            echo "</div>";
        }
    } else {
        $reset_error = "❌ Invalid or expired token!";
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    unset($_SESSION['logged_in_user']);
    $logout_msg = "✅ Logged out successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🎯 Host Header Injection Lab</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .form-section {
            background: #f8f9fa;
            border: 2px solid #28a745;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .reset-section { border-color: #fd7e14; }
        
        .form-group {
            margin: 15px 0;
        }
        
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        
        input[type="email"], input[type="password"] { 
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-login { background: #28a745; color: white; }
        .btn-login:hover { background: #218838; }
        
        .btn-reset { background: #fd7e14; color: white; }
        .btn-reset:hover { background: #e55a00; }
        
        .btn-logout { background: #dc3545; color: white; }
        .btn-logout:hover { background: #c82333; }
        
        .btn-secondary { 
            background: #6c757d; 
            color: white; 
            width: auto; 
            padding: 8px 16px; 
            font-size: 14px;
            margin-top: 5px;
        }
        .btn-secondary:hover { background: #5a6268; }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .info {
            background: #cce7ff;
            color: #004085;
            padding: 10px;
            border: 1px solid #b3d7ff;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        code {
            background: #f8f8f8;
            padding: 3px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        h1 { 
            text-align: center; 
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }
        
        h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .forgot-password-link {
            text-align: right;
            margin-top: 10px;
        }
        
        .forgot-password-link button {
            width: auto;
            padding: 6px 12px;
            font-size: 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .forgot-password-link button:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <h1>🎯 Host Header Injection Lab</h1>
    <p style="color: white; text-align: center; font-size: 18px;">Login to Get the FLAG</p>
    
    <?php if (isset($_SESSION['logged_in_user'])): ?>
        <div class="container">
            <div class="info">
                <h3>👋 Welcome back, <?= htmlspecialchars($_SESSION['logged_in_user']) ?>!</h3>
                <p>You are currently logged in.</p>
                <form method="post" style="display: inline;">
                    <button type="submit" name="logout" class="btn-logout">🚪 Logout</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($logout_msg)): ?>
        <div class="container">
            <div class="success"><?= $logout_msg ?></div>
        </div>
    <?php endif; ?>

    <div class="container">
        <!-- Login Section -->
        <div class="form-section">
            <h2>🔐 User Login</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="error"><?= $login_error ?></div>
            <?php endif; ?>
            
            <?php if (isset($login_success)): ?>
                <div class="success"><?= $login_success ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="login_email">Email Address:</label>
                    <input type="email" id="login_email" name="email" placeholder="victim@example.com" value="victim@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="login_password">Password:</label>
                    <input type="password" id="login_password" name="password" placeholder="Enter your password" required>
                    <div class="forgot-password-link">
                        <button type="button" onclick="document.getElementById('reset-section').scrollIntoView(); document.getElementById('reset_email').focus();" class="btn-secondary">
                            🔄 Forgot Password?
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="btn-login">🚀 Login to Get FLAG</button>
            </form>
            
            <div class="info" style="margin-top: 15px;">
                <strong>Test Account:</strong><br>
                Email: victim@example.com<br>
                Default Password: original123<br>
                <small>Use Host Header Injection attack to change password and get the FLAG!</small>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Password Reset Section -->
        <?php if(!isset($_GET['token'])): ?>
            <div class="form-section reset-section" id="reset-section">
                <h2>🔄 Reset Password</h2>
                
                <?php if (isset($reset_error)): ?>
                    <div class="error"><?= $reset_error ?></div>
                <?php endif; ?>
                
                <p>Enter your email to receive a password reset link:</p>
                
                <form method="post">
                    <div class="form-group">
                        <label for="reset_email">Email Address:</label>
                        <input type="email" id="reset_email" name="email" placeholder="victim@example.com" value="victim@example.com" required>
                    </div>
                    
                    <button type="submit" name="request_reset" class="btn-reset">📧 Send Reset Link</button>
                </form>
                
                <div class="info" style="margin-top: 15px;">
                    <strong>⚠️ Vulnerability:</strong> This form is vulnerable to Host Header Injection attacks!<br>
                    <small>Use curl with malicious Host header to exploit this vulnerability.</small>
                </div>
            </div>
        <?php else: ?>
            <div class="form-section reset-section">
                <h2>🔒 Reset Your Password</h2>
                
                <?php if (isset($reset_error)): ?>
                    <div class="error"><?= $reset_error ?></div>
                <?php endif; ?>
                
                <p><strong>Reset Token:</strong> <code><?= htmlspecialchars($_GET['token']) ?></code></p>
                
                <form method="post">
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" required>
                    </div>
                    
                    <button type="submit" class="btn-reset">✅ Reset Password</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

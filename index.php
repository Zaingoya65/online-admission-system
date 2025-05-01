<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

include './db/db_connection.php';

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, email, password, is_verified FROM registeredusers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (!$user['is_verified']) {
                $error = 'Please verify your email before logging in.';
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: pages/adcriteria.php");
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again.';
    }
}

// Handle forgot password request
if (isset($_GET['forgot'])) {
    $email = trim($_GET['email'] ?? '');
    
    if (!empty($email)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $pdo->prepare("UPDATE registeredusers SET reset_token = ?, token_expiry = ? WHERE id = ?");
                $stmt->execute([$token, $expiry, $user['id']]);

                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=$token";

                $headers = "From: admissions@alhijrah.pk\r\n";
                $headers .= "Reply-To: admissions@alhijrah.pk\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                $subject = 'Password Reset Request - AHRSC';
                $message = "
                    <html>
                    <head>
                        <title>Password Reset</title>
                    </head>
                    <body>
                        <h2>Password Reset Request</h2>
                        <p>We received a request to reset your password. Click the link below to reset it:</p>
                        <p style='text-align: center; margin: 20px 0;'>
                            <a href='$reset_link' style='background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a>
                        </p>
                        <p>Or copy and paste this link into your browser:<br>
                        <code>$reset_link</code></p>
                        <p>This link will expire in 1 hour.</p>
                        <p>If you didn't request this, please ignore this email.</p>
                    </body>
                    </html>
                ";

                if (mail($email, $subject, $message, $headers)) {
                    $success = 'Password reset link has been sent to your email.';
                } else {
                    $error = 'Failed to send reset email. Please try again.';
                }
            } else {
                $error = 'No account found with that email address.';
            }
        } catch (PDOException $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login - Alhijrah AHRSC</title>
    <script>
        function showForgotPassword() {
            const email = prompt("Please enter your email address:");
            if (email) {
                window.location.href = "login.php?forgot=1&email=" + encodeURIComponent(email);
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden p-8">
            <div class="text-center mb-8">
                <img src="./assets/images/logo.png" alt="AHRSC Logo" class="h-16 mx-auto">
                <h2 class="mt-4 text-2xl font-bold text-gray-800">Login to AHRSC Admissions</h2>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form class="space-y-4" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="text-right">
                    <a href="javascript:void(0);" onclick="showForgotPassword()" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
                </div>
                
                <div class="pt-4">
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </button>
                </div>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
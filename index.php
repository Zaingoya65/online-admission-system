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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
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
            error_log("Database Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
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
            error_log("Database Error: " . $e->getMessage());
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
<body>
    <div class="mt-8 min-h-full flex-col justify-center px-6 py-12 lg:px-8 max-w-md w-full mx-auto border border-slate-300 rounded-2xl p-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-30 w-auto" src="assets/images/logo.png" alt="AL-Hijrah logo">
            <h3 class="mt-1 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Welcome Again! AHRSC - Login</h3>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
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

        <?php if (!empty($success)): ?>
            <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-4">
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

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form class="space-y-6" action="login.php" method="POST">
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" autocomplete="email" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                        <div class="text-sm">
                            <a href="javascript:void(0);" onclick="showForgotPassword()" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                        </div>
                    </div>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign
                        in</button>
                </div>
            </form>

            <p class="mt-10 text-center text-sm/6 text-gray-500">
                Don't have an account? <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Register
                    here</a>
            </p>
        </div>
    </div>
</body>
</html>
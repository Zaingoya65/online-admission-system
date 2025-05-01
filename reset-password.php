<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

include './db/db_connection.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE reset_token = ? AND token_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE registeredusers SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
                $updateStmt->execute([$hashed_password, $user['id']]);
                
                $success = 'Your password has been reset successfully. You can now login with your new password.';
            } else {
                $error = 'Invalid or expired reset link. Please request a new password reset.';
            }
        } catch (PDOException $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
} elseif (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->execute([$token]);
        if (!$stmt->fetch()) {
            $error = 'Invalid or expired reset link. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        error_log("Token Verification Error: " . $e->getMessage());
        $error = 'An error occurred. Please try again.';
    }
} else {
    $error = 'Invalid reset link.';
}
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Reset Password - AHRSC</title>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden p-8">
            <div class="text-center mb-8">
                <img src="./assets/images/logo.png" alt="AHRSC Logo" class="h-16 mx-auto">
                <h2 class="mt-4 text-2xl font-bold text-gray-800">Reset Password</h2>
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
                <div class="text-center">
                    <a href="index.php" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Login Now</a>
                </div>
            <?php elseif (empty($error)): ?>
                <form class="space-y-4" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" name="password" id="password" required minlength="6"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Reset Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
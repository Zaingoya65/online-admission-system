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

// Handle password reset form submission
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
            // Check if token is valid and not expired
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
            error_log("Database Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
} elseif (!empty($token)) {
    // Verify token when page loads
    try {
        $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Invalid or expired reset link. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
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
    <title>Reset Password - Alhijrah AHRSC</title>
</head>
<body>
    <div class="mt-8 min-h-full flex-col justify-center px-6 py-12 lg:px-8 max-w-md w-full mx-auto border border-slate-300 rounded-2xl p-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-30 w-auto" src="assets/images/logo.png" alt="AL-Hijrah logo">
            <h3 class="mt-1 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Reset Password</h3>
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
            <div class="mt-6 text-center">
                <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Back to Login</a>
            </div>
        <?php elseif (empty($error)): ?>
            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form class="space-y-6" action="reset-password.php" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div>
                        <label for="password" class="block text-sm/6 font-medium text-gray-900">New Password</label>
                        <div class="mt-2">
                            <input type="password" name="password" id="password" required minlength="6"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm/6 font-medium text-gray-900">Confirm New Password</label>
                        <div class="mt-2">
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                                class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Reset Password</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
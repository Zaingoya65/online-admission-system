<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

include './db/db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';
$pending = isset($_GET['pending']);
$email = $_GET['email'] ?? '';
$resend = isset($_GET['resend']);
$success = false;
$error = '';
$already_verified = false;

// Handle resend verification email
if ($resend && !empty($email)) {
    try {
        $stmt = $pdo->prepare("SELECT verification_token FROM registeredusers WHERE email = ? AND is_verified = 0");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $verification_link = "https://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $user['verification_token'];
            
            $headers = "From: admissions@alhijrah.edu.pk\r\n";
            $headers .= "Reply-To: admissions@alhijrah.edu.pk\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $subject = 'Verify Your Email - AHRSC Admissions';
            $message = "Your verification link: $verification_link";

            if (mail($email, $subject, $message, $headers)) {
                header("Location: verify.php?pending=1&email=" . urlencode($email) . "&resent=1");
                exit;
            } else {
                $error = "Failed to resend verification email.";
            }
        } else {
            $error = "Email already verified or doesn't exist.";
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = "An error occurred while resending verification email.";
    }
}

// Handle email verification
if (!empty($token)) {
    try {
        // Check if user is already verified
        $checkVerified = $pdo->prepare("SELECT is_verified FROM registeredusers WHERE verification_token = ?");
        $checkVerified->execute([$token]);
        $verificationStatus = $checkVerified->fetch();

        if ($verificationStatus && $verificationStatus['is_verified']) {
            // If already verified, redirect to login
            header("Location: index.php");
            exit;
        } elseif (!$verificationStatus) {
            $error = "Verification link is invalid.";
        } else {
            // Verify the user
            $stmt = $pdo->prepare("SELECT id, email FROM registeredusers WHERE verification_token = ? AND token_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $updateStmt = $pdo->prepare("UPDATE registeredusers SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Set session flag and redirect to prevent refresh issues
                $_SESSION['verified_email'] = $user['email'];
                $_SESSION['just_verified'] = true;
                header("Location: index.php");
                exit;
            } else {
                $error = "Verification link has expired.";
            }
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = "An error occurred during verification.";
    }
}

// If pending verification page is refreshed, show appropriate message
if ($pending && !empty($email)) {
    try {
        $stmt = $pdo->prepare("SELECT is_verified FROM registeredusers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $user['is_verified']) {
            // If already verified, redirect to login
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AHRSC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden p-8">
            <div class="text-center mb-6">
                <img src="./assets/images/logo.png" alt="AHRSC Logo" class="h-16 mx-auto">
                <h1 class="mt-4 text-2xl font-bold text-gray-800">Email Verification</h1>
            </div>
            
            <?php if (isset($_GET['resent'])): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                Verification email has been resent successfully!
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($pending): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                We've sent a verification link to <strong><?= htmlspecialchars($email) ?></strong>. 
                                Please check your email and click the link to verify your account.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-4">Didn't receive the email?</p>
                    <a href="verify.php?resend=1&email=<?= urlencode($email) ?>" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Resend Verification Email
                    </a>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?= htmlspecialchars($error) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-sm text-gray-600">Need help? <a href="contact.php" class="text-blue-600 hover:underline">Contact support</a></p>
                    <p class="mt-2 text-sm text-gray-600">Or <a href="register.php" class="text-blue-600 hover:underline">register again</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
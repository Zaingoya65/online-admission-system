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
$success = false;
$error = '';

if (!empty($token)) {
    try {
        // Check if token is valid and not expired
        $stmt = $pdo->prepare("SELECT id, email FROM registeredusers WHERE verification_token = ? AND token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Update user as verified
            $updateStmt = $pdo->prepare("UPDATE registeredusers SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            $success = true;
            $_SESSION['verified_email'] = $user['email'];
        } else {
            $error = "Verification link is invalid or has expired.";
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = "An error occurred during verification. Please try again.";
    }
} else {
    $error = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AHRSC</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0069d9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Verification</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <p>Your email has been successfully verified!</p>
                <p>You can now login to your account.</p>
            </div>
            <a href="login.php" class="btn">Proceed to Login</a>
        <?php else: ?>
            <div class="error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
            <div class="mt-4">
                <p>Need help? <a href="contact.php">Contact support</a></p>
                <p>Or <a href="register.php">register again</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
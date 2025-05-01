<?php
include './db/db_connection.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid or missing verification token.");
}

try {
    $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE verification_token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE registeredusers SET is_verified = TRUE, verification_token = NULL, token_expiry = NULL WHERE verification_token = ?");
        $stmt->execute([$token]);

        echo "Your email has been successfully verified. <a href='login.php'>Login here</a>";
    } else {
        echo "Verification link is invalid or has expired.";
    }
} catch (PDOException $e) {
    echo "Database error.";
}

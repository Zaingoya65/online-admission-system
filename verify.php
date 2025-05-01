<?php
require_once "db/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    $stmt = $pdo->prepare("SELECT * FROM registeredusers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            echo "Already verified.";
        } elseif ($user['verification_token'] === $otp && strtotime($user['token_expiry']) > time()) {
            $update = $pdo->prepare("UPDATE registeredusers SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE email = ?");
            $update->execute([$email]);
            echo "Email verified successfully!";
            // You can redirect to login page here
        } else {
            echo "Invalid or expired OTP.";
        }
    } else {
        echo "User not found.";
    }
} else {
    $email = $_GET['email'] ?? '';
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <h2>Enter OTP sent to your email</h2>
    <form method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <label>OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>

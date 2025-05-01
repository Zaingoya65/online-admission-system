<?php
session_start();
if (!isset($_SESSION['register_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['register_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    include './db/db_connection.php';
    $otp = $_POST['otp'];

    $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE email = ? AND verification_token = ? AND token_expiry > NOW()");
    $stmt->execute([$email, $otp]);

    if ($stmt->rowCount() > 0) {
        $pdo->prepare("UPDATE registeredusers SET is_verified = TRUE, verification_token = NULL, token_expiry = NULL WHERE email = ?")->execute([$email]);
        unset($_SESSION['register_email']);
        header('Location: login.php');
        exit;
    } else {
        $error = "Invalid or expired OTP.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    include './db/db_connection.php';
    require './phpmailer/src/Exception.php';
    require './phpmailer/src/PHPMailer.php';
    require './phpmailer/src/SMTP.php';

    $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $pdo->prepare("UPDATE registeredusers SET verification_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = ?")
        ->execute([$otp, $email]);

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->setFrom('admissions@alhijrah.pk', 'AHRSC Admissions');
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for AHRSC Registration';
        $mail->Body = "Your One-Time Password (OTP) is: <b>$otp</b><br><br>This code will expire in 15 minutes.";
        $mail->send();

        $success = "OTP resent successfully!";
    } catch (Exception $e) {
        $error = "Failed to resend OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Verify Email</title></head>
<body>
    <h2>Email Verification</h2>
    <p>We’ve sent a verification code to your email: <strong><?= htmlspecialchars($email) ?></strong></p>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="POST">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" id="otp" required>
        <button type="submit">Verify</button>
    </form>

    <form method="POST" style="margin-top:1em;">
        <input type="hidden" name="resend" value="1">
        <button type="submit">Resend OTP</button>
    </form>
</body>
</html>

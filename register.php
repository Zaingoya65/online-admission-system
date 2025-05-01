<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

include './db/db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $errors = [];

    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['cpassword'] ?? '';

    // Validations
    if (empty($full_name)) $errors['name'] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email address.';
    if (empty($cnic)) $errors['cnic'] = 'CNIC/B-form is required.';
    if (empty($password) || strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
    if ($password !== $confirm_password) $errors['cpassword'] = 'Passwords do not match.';

    if (empty($errors)) {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE email = ? OR cnic = ?");
            $stmt->execute([$email, $cnic]);

            if ($stmt->rowCount() > 0) {
                $errors['general'] = 'Email or CNIC already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $stmt = $pdo->prepare("INSERT INTO registeredusers (full_name, email, cnic, password, verification_token, token_expiry) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $cnic, $hashed_password, $token, $expiry]);

                $_SESSION['register_email'] = $email;

                // Generate verification link
                $verification_link = "https://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=$token";

                // Email headers
                $headers = "From: admissions@alhijrah.edu.pk\r\n";
                $headers .= "Reply-To: admissions@alhijrah.edu.pk\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                // Email subject and body
                $subject = 'Verify Your Email - AHRSC Admissions';
                $message = "
                    <html>
                    <head>
                        <title>Email Verification</title>
                    </head>
                    <body>
                        <h2>Email Verification</h2>
                        <p>Dear $full_name,</p>
                        <p>Thank you for registering with AHRSC Admissions Portal. Please verify your email address by clicking the button below:</p>
                        <p style='text-align: center; margin: 20px 0;'>
                            <a href='$verification_link' style='background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a>
                        </p>
                        <p>Or copy and paste this link into your browser:<br>
                        <code>$verification_link</code></p>
                        <p>This link will expire in 24 hours.</p>
                        <p>If you didn't request this registration, please ignore this email.</p>
                    </body>
                    </html>
                ";

                // Send email
                if (mail($email, $subject, $message, $headers)) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Registration successful! Please check your email to verify your account.',
                        'redirect' => 'verify.php?pending=1&email=' . urlencode($email)
                    ]);
                    exit;
                } else {
                    error_log("Failed to send verification email to $email");
                    $errors['general'] = 'Failed to send verification email. Please try again later.';
                }
            }



            

            if (mail($email, $subject, $message, $headers)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Registration successful! Please check your email to verify your account.',
                    'redirect' => 'verify.php?pending=1&email=' . urlencode($email)
                ]);
                exit;
            } else {
                error_log("Failed to send verification email to $email");
                $errors['general'] = 'Failed to send verification email. Please try again later.';
            }







        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }

    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Register - Alhijrah AHRSC</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const cnicInput = document.getElementById('cnic');

            // CNIC formatting
            cnicInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5);
                }
                if (value.length > 13) {
                    value = value.substring(0, 13) + '-' + value.substring(13);
                }
                if (value.length > 15) {
                    value = value.substring(0, 15);
                }
                
                e.target.value = value;
            });

            // Form submission
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                
                try {
                    const response = await fetch('register.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (result.redirect) {
                            window.location.href = result.redirect;
                        } else {
                            alert(result.message);
                        }
                    } else {
                        // Clear previous errors
                        document.querySelectorAll('.error-message').forEach(el => el.remove());
                        
                        if (result.errors) {
                            Object.entries(result.errors).forEach(([field, message]) => {
                                const input = form.querySelector(`[name="${field}"]`);
                                if (input) {
                                    const errorElement = document.createElement('p');
                                    errorElement.className = 'error-message mt-1 text-sm text-red-600';
                                    errorElement.textContent = message;
                                    input.closest('div').appendChild(errorElement);
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create an account';
                }
            });
        });
    </script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md overflow-hidden p-8">
            <div class="text-center mb-8">
                <img src="./assets/images/logo.png" alt="AHRSC Logo" class="h-16 mx-auto">
                <h2 class="mt-4 text-2xl font-bold text-gray-800">
                    Register for <span class="text-blue-600">AHRSC Admissions</span>
                </h2>
            </div>
            
            <form class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="cnic" class="block text-sm font-medium text-gray-700">B-form/CNIC</label>
                    <input type="text" name="cnic" id="cnic" required placeholder="XXXXX-XXXXXXX-X"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required minlength="6"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="cpassword" id="cpassword" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="pt-4">
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create an account
                    </button>
                </div>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account? 
                <a href="index.php" class="font-medium text-blue-600 hover:text-blue-500">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
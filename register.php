<?php
header('Content-Type: application/json');
error_reporting(E_ERROR | E_PARSE);

include './db/db_connection.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $email = $_SESSION['register_email'] ?? '';
    $user_otp = $_POST['otp'] ?? '';
    
    if (empty($email) || empty($user_otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    try {
        // Check if OTP matches and isn't expired
        $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE email = ? AND verification_token = ? AND token_expiry > NOW()");
        $stmt->execute([$email, $user_otp]);
        
        if ($stmt->rowCount() > 0) {
            // Mark user as verified
            $stmt = $pdo->prepare("UPDATE registeredusers SET is_verified = TRUE, verification_token = NULL, token_expiry = NULL WHERE email = ?");
            $stmt->execute([$email]);
            
            // Clear session data
            unset($_SESSION['register_email']);
            unset($_SESSION['otp_verified']);
            
            echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['verify_otp'])) {
    // Validate and sanitize input
    $errors = [];
    
    $full_name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['cpassword'] ?? '';
    
    // Validate inputs (same as before)
    // ... [keep all your existing validation code] ...
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if email or CNIC already exists
            $stmt = $pdo->prepare("SELECT id FROM registeredusers WHERE email = ? OR cnic = ?");
            $stmt->execute([$email, $cnic]);
            
            if ($stmt->rowCount() > 0) {
                $errors['general'] = 'Email or CNIC already registered';
				

            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate OTP (6 digits)
                $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                
                // Insert new user (not verified yet)
                $stmt = $pdo->prepare("INSERT INTO registeredusers (full_name, email, cnic, password, verification_token, token_expiry) 
                                      VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
                $stmt->execute([$full_name, $email, $cnic, $hashed_password, $otp]);
                
                // Store email in session for verification
                $_SESSION['register_email'] = $email;
                
                // Send OTP to user (in production, use email/SMS service)
                // This is just a simulation - in real app, use PHPMailer or similar
              
                require './phpmailer/src/Exception.php';
                require './phpmailer/src/PHPMailer.php';
                require './phpmailer/src/SMTP.php';
                
                $mail = new PHPMailer(true);
                
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.hostinger.com'; // Hostinger SMTP
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'your_email@yourdomain.com'; // your email on Hostinger
                    $mail->Password   = 'your_email_password'; // your email's password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;
                
                    //Recipients
                    $mail->setFrom('admissions@alhijrah.pk', 'AHRSC Admissions');
                    $mail->addAddress($email); // Recipient
                
                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for AHRSC Registration';
                    $mail->Body    = "Your One-Time Password (OTP) is: <b>$otp</b><br><br>This code will expire in 15 minutes.";
                
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
                
                
                // Return success with OTP verification flag
                echo json_encode([
                    'success' => true, 
                    'requires_otp' => true,
                    'message' => 'Registration pending OTP verification'
                ]);
                exit;
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // If there are errors, return them
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// If not a POST request, show the appropriate form
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Regist  Alhijrah AHRSC</title>
    <script>
        let otpVerificationMode = false;
        
        async function handleSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
			const submitBtn = form.querySelector('button');

            
            // Disable button during submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            try {
                const endpoint = otpVerificationMode ? 'register.php?verify=1' : 'register.php';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.requires_otp) {
                        // Switch to OTP verification UI
                        showOtpVerification();
                    } else {
                        alert(result.message);
                        window.location.href = 'login.php';
                    }
                } else {
                    // Display errors
                    if (result.errors) {
						Object.keys(result.errors).forEach(field => {
    const input = form.querySelector(`[name="${field}"]`);
    
    if (input) {
        const errorElement = document.createElement('p');
        errorElement.className = 'mt-1 text-sm text-red-600';
        errorElement.textContent = result.errors[field];

        const existingError = input.nextElementSibling;
        if (existingError && existingError.className.includes('text-red-600')) {
            existingError.remove();
        }

        input.insertAdjacentElement('afterend', errorElement);
    } else {
        // Fallback for general errors or unknown fields
        alert(result.errors[field]);
    }
});

                            
                          
                    } else if (result.message) {
                        alert(result.message);
                    }
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error(error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = otpVerificationMode ? 'Verify OTP' : 'Create an account';
            }
        }
        
        function showOtpVerification() {
            otpVerificationMode = true;
            const form = document.querySelector('form');
            
            // Change form title
            document.querySelector('h2').innerHTML = 'Verify Email <span class="text-blue-600 font-medium hover:underline ml-1">Enter OTP</span>';
            
            // Hide all form elements except OTP field
            const allInputs = form.querySelectorAll('div:not(.otp-field)');
            allInputs.forEach(el => el.style.display = 'none');
            
            // Create OTP field if not exists
            let otpField = form.querySelector('.otp-field');
            if (!otpField) {
                otpField = document.createElement('div');
                otpField.className = 'otp-field mt-6';
                otpField.innerHTML = `
                    <label for="otp" class="block text-sm/6 font-medium text-gray-900">OTP (Check your email)</label>
                    <div class="mt-2">
                        <input type="text" name="otp" id="otp" required 
                               class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 
                               outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                               focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">We've sent a 6-digit code to your email address.</p>
                    <input type="hidden" name="verify_otp" value="1">
                `;
                form.insertBefore(otpField, form.querySelector('button').parentElement);
            } else {
                otpField.style.display = 'block';
            }
            
            // Update button text
            form.querySelector('button').textContent = 'Verify OTP';
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
    if (form) {
        console.log('Form event listener added'); 
        form.addEventListener('submit', handleSubmit);
    } else {
        console.error('Form not found');
    }
            // Add input masking for CNIC
            const cnicInput = document.getElementById('cnic');
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
        });
    </script>
</head>

<body>
    <div class="flex flex-col justify-center p-4">
        <div class="max-w-md w-full mx-auto border border-slate-300 rounded-2xl p-8">
            <div class="text-center mb-6">
                <a href="javascript:void(0)"><img src="./assets/images/logo.png" alt="logo" class="w-30 inline-block" /> </a>
                <h2 class="mt-1">
                    Register<span class="text-blue-600 font-medium hover:underline ml-1">AHRSC Admission Portal</span>!
                </h2> 
            </div>
            <form>
                <div>
                    <label for="name" class="block text-sm/6 font-medium text-gray-900">Full Name</label>
                    <div class="mt-2">
                        <input type="text" name="name" id="name" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> 
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" autocomplete="email" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> 
                    </div>
                </div>
                <div>
                    <label for="cnic" class="block text-sm/6 font-medium text-gray-900">B-form/CNIC</label>
                    <div class="mt-2">
                        <input type="text" name="cnic" id="cnic" required placeholder="XXXXX-XXXXXXX-X" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> 
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> 
                    </div>
                </div>
                <div>
                    <label for="cpassword" class="block text-sm/6 font-medium text-gray-900">Confirm password</label>
                    <div class="mt-2">
                        <input type="password" name="cpassword" id="cpassword" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> 
                    </div>
                </div>
                <div class="mt-12">
                    <button type="submit" class="w-full py-3 px-4 text-sm tracking-wider font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"> Create an account </button>
                </div>
                <p class="text-slate-800 text-sm mt-6 text-center">Already have an account? <a href="login.php" class="text-blue-600 font-medium hover:underline ml-1">Login here</a></p>
            </form>
        </div>
    </div>
</body>
</html>
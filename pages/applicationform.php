<?php 
include '../session_auth.php'; 
include '../db/db_connection.php';

// Initialize session variables
$_SESSION['form_errors'] = $_SESSION['form_errors'] ?? [];
$_SESSION['form_data'] = $_SESSION['form_data'] ?? [];

// Check if editing an existing application
$isEditMode = false;
$existingApplication = null;
$existingPhotoPath = null;

if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
        $existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingApplication) {
            $isEditMode = true;
            $existingPhotoPath = $existingApplication['photo_path'];
            // Populate form data from existing application
            $_SESSION['form_data'] = [
                'fullName' => $existingApplication['full_name'],
                'fatherName' => $existingApplication['father_name'],
                'bForm' => $existingApplication['b_form'],
                'fatherCNIC' => $existingApplication['father_cnic'],
                'dob' => $existingApplication['dob'],
                'guardianOccupation' => $existingApplication['guardian_occupation'],
                'postalAddress' => $existingApplication['postal_address'],
                'lastSchool' => $existingApplication['last_school'],
                'gradeMarks' => $existingApplication['grade_marks'],
                'totalMarks' => $existingApplication['total_marks'],
                'passingDate' => $existingApplication['passing_date'],
                'contactNo' => $existingApplication['contact_no'],
                'emergencyContact' => $existingApplication['emergency_contact'],
                'email' => $existingApplication['email'],
                'confirmation' => 'on'
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['form_errors'] = ["Error loading application: " . $e->getMessage()];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required = ['fullName', 'fatherName', 'bForm', 'fatherCNIC', 'dob', 'guardianOccupation', 
                'postalAddress', 'lastSchool', 'gradeMarks', 'totalMarks', 'passingDate',
                'contactNo', 'emergencyContact', 'email'];
    
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . " is required.";
        }
    }
    
    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Validate confirmation checkbox
    if (!isset($_POST['confirmation'])) {
        $errors[] = "You must confirm the information is accurate.";
    }
    
    // Validate marks
    if (!empty($_POST['gradeMarks']) && !empty($_POST['totalMarks']) && 
        $_POST['gradeMarks'] > $_POST['totalMarks']) {
        $errors[] = "Obtained marks cannot be greater than total marks.";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        $photoPath = $existingPhotoPath;
        
        // Handle file upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (in_array($_FILES['photo']['type'], $allowedTypes) && $_FILES['photo']['size'] <= $maxSize) {
                $uploadDir = '../uploads/applications/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Remove old photo if exists
                if ($photoPath && file_exists($uploadDir . $photoPath)) {
                    unlink($uploadDir . $photoPath);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $photoPath = $filename;
                } else {
                    $errors[] = "Failed to upload photo";
                }
            } else {
                $errors[] = "Invalid photo type or size (JPEG/PNG, Max 2MB)";
            }
        } elseif (!$photoPath && !$isEditMode) {
            $errors[] = "Student photo is required";
        }
        
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: applicationform.php' . ($isEditMode ? '?edit=' . $_GET['edit'] : ''));
            exit;
        }
        
        // Prepare data for database
        $data = [
            'full_name' => $_POST['fullName'],
            'father_name' => $_POST['fatherName'],
            'b_form' => $_POST['bForm'],
            'father_cnic' => $_POST['fatherCNIC'],
            'dob' => $_POST['dob'],
            'guardian_occupation' => $_POST['guardianOccupation'],
            'postal_address' => $_POST['postalAddress'],
            'last_school' => $_POST['lastSchool'],
            'grade_marks' => $_POST['gradeMarks'],
            'total_marks' => $_POST['totalMarks'],
            'passing_date' => $_POST['passingDate'],
            'contact_no' => $_POST['contactNo'],
            'emergency_contact' => $_POST['emergencyContact'],
            'email' => $_POST['email'],
            'photo_path' => $photoPath,
            'user_id' => $_SESSION['user_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            if ($isEditMode) {
                // Update existing application
                $data['id'] = $_GET['edit'];
                $stmt = $pdo->prepare("
                    UPDATE applications SET
                        full_name = :full_name, 
                        father_name = :father_name, 
                        b_form = :b_form,
                        father_cnic = :father_cnic, 
                        dob = :dob, 
                        guardian_occupation = :guardian_occupation,
                        postal_address = :postal_address, 
                        last_school = :last_school, 
                        grade_marks = :grade_marks, 
                        total_marks = :total_marks, 
                        passing_date = :passing_date,
                        contact_no = :contact_no, 
                        emergency_contact = :emergency_contact, 
                        email = :email, 
                        photo_path = :photo_path, 
                        updated_at = :updated_at
                    WHERE id = :id AND user_id = :user_id
                ");
                
                $data['user_id'] = $_SESSION['user_id'];
                $stmt->execute($data);
            } else {
                // Create new application
                $data['submitted_at'] = date('Y-m-d H:i:s');
                $data['status'] = 'pending';
                
                $stmt = $pdo->prepare("
                    INSERT INTO applications (
                        full_name, father_name, b_form, father_cnic, dob, guardian_occupation,
                        postal_address, last_school, grade_marks, total_marks, passing_date,
                        contact_no, emergency_contact, email, photo_path, user_id, submitted_at, status
                    ) VALUES (
                        :full_name, :father_name, :b_form, :father_cnic, :dob, :guardian_occupation,
                        :postal_address, :last_school, :grade_marks, :total_marks, :passing_date,
                        :contact_no, :emergency_contact, :email, :photo_path, :user_id, :submitted_at, :status
                    )
                ");
                
                $stmt->execute($data);
                $applicationId = $pdo->lastInsertId();
            }
            
            // Clear form data and errors
            unset($_SESSION['form_data']);
            unset($_SESSION['form_errors']);
            
            // Redirect to success page
            header('Location: application_success.php?id=' . ($isEditMode ? $_GET['edit'] : $applicationId));
            exit;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['form_errors'] = ["A database error occurred. Please try again."];
            $_SESSION['form_data'] = $_POST;
            header('Location: applicationform.php' . ($isEditMode ? '?edit=' . $_GET['edit'] : ''));
            exit;
        }
    } else {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: applicationform.php' . ($isEditMode ? '?edit=' . $_GET['edit'] : ''));
        exit;
    }
}

// Fetch user data
$userData = [];
try {
    $stmt = $pdo->prepare("SELECT full_name, cnic, email FROM registeredusers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $userData = ['full_name' => '', 'cnic' => '', 'email' => ''];
}
?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title><?= $isEditMode ? 'Edit' : 'New'; ?> Application - Alhijrah AHRSC</title>
  <style>
    .image-upload-container {
      position: relative;
      width: 150px;
      height: 150px;
      border: 2px dashed #d1d5db;
      border-radius: 50%;
      overflow: hidden;
      margin: 0 auto;
      transition: all 0.3s ease;
      background-color: #f9fafb;
    }

    .image-upload-container.has-image {
      border-color: #818cf8;
      border-style: solid;
    }

    .image-upload-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      cursor: pointer;
      text-align: center;
      color: #6b7280;
    }

    .image-upload-icon {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      color: #9ca3af;
    }

    .image-upload-preview {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .image-upload-change,
    .image-upload-remove {
      position: absolute;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .image-upload-change {
      bottom: 10px;
      right: 10px;
      color: #4f46e5;
    }

    .image-upload-remove {
      top: 10px;
      right: 10px;
      color: #ef4444;
    }

    .image-upload-change:hover,
    .image-upload-remove:hover {
      transform: scale(1.1);
    }

    .image-upload-container:not(.has-image) .image-upload-change,
    .image-upload-container:not(.has-image) .image-upload-remove {
      display: none;
    }

    .cnic-input {
      letter-spacing: 1px;
    }
  </style>
  <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        
        if (file) {
            // Check file type and size
            const validTypes = ['image/jpeg', 'image/png'];
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!validTypes.includes(file.type)) {
                alert('Please upload a JPEG or PNG image (max 2MB)');
                input.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                alert('Image size exceeds 2MB limit');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" class="image-upload-preview" alt="Preview">
                    <span class="image-upload-change">
                        <i class="fas fa-sync-alt"></i>
                    </span>
                    <span class="image-upload-remove" onclick="removeImage(this)">
                        <i class="fas fa-times"></i>
                    </span>
                `;
                preview.classList.add('has-image');
            }
            
            reader.readAsDataURL(file);
        }
    }

    function removeImage(button) {
        const container = button.closest('.image-upload-container');
        const input = document.getElementById('photo');
        
        container.innerHTML = `
            <div class="image-upload-label">
                <i class="fas fa-camera image-upload-icon"></i>
                <p class="text-xs">Click to upload</p>
                <p class="text-xxs mt-1">JPEG/PNG, Max 2MB</p>
            </div>
        `;
        container.classList.remove('has-image');
        input.value = '';
    }

    function formatCNIC(input) {
        // Format as XXXXX-XXXXXXX-X
        let value = input.value.replace(/\D/g, '');
        if (value.length > 13) value = value.substring(0, 13);
        
        let formatted = '';
        if (value.length > 5) {
            formatted = value.substring(0, 5) + '-' + value.substring(5);
            if (value.length > 12) {
                formatted = formatted.substring(0, 13) + '-' + formatted.substring(13);
            }
        } else {
            formatted = value;
        }
        input.value = formatted;
    }
    
    function formatPhone(input) {
        // Format as XXXX-XXXXXXX
        let value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        
        let formatted = '';
        if (value.length > 4) {
            formatted = value.substring(0, 4) + '-' + value.substring(4);
        } else {
            formatted = value;
        }
        input.value = formatted;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($isEditMode && $existingPhotoPath): ?>
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = `
                <img src="../uploads/applications/<?= $existingPhotoPath ?>?<?= time() ?>" class="image-upload-preview" alt="Preview">
                <span class="image-upload-change">
                    <i class="fas fa-sync-alt"></i>
                </span>
                <span class="image-upload-remove" onclick="removeImage(this)">
                    <i class="fas fa-times"></i>
                </span>
            `;
            preview.classList.add('has-image');
        <?php endif; ?>
    });
  </script>
</head>
<body class="bg-gray-100">
<?php if (!empty($_SESSION['form_errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <?php foreach ($_SESSION['form_errors'] as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Mobile menu button -->
<button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 text-gray-600 p-2">
  <i class="fas fa-bars" id="menuIcon"></i>
</button>

<div class="flex">
  <?php include '../partials/sidebar.php'; ?>
  
  <div class="flex-1">
    <?php include '../partials/header.php'; ?>

    <div class="p-6 md:p-8">
      <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
              <span class="bg-indigo-100 p-2 rounded-full mr-3">
                <i class="fas fa-user-graduate text-indigo-600"></i>
              </span>
              <?= $isEditMode ? 'Edit Application' : 'New Application' ?>
            </h2>
            <?php if ($isEditMode): ?>
              <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                <i class="fas fa-edit mr-1"></i> Editing Mode
              </span>
            <?php endif; ?>
          </div>
          <div class="bg-blue-50 p-3 rounded-md mt-3">
            <h3 class="text-sm font-medium text-blue-800 flex items-center">
              <i class="fas fa-info-circle text-blue-500 mr-2"></i>
              Important Instructions
            </h3>
            <ul class="text-xs text-blue-700 mt-1 list-disc list-inside space-y-1">
              <li>All fields marked with <span class="text-red-500">*</span> are mandatory</li>
              <li>Please fill the form carefully with accurate information</li>
              <li>You will need to upload supporting documents in the next step</li>
              <li>Incomplete or incorrect applications may be rejected</li>
            </ul>
          </div>
        </div>
        
        <form class="space-y-6" action="" method="POST" enctype="multipart/form-data">
          <?php if ($isEditMode): ?>
            <input type="hidden" name="edit_mode" value="1">
          <?php endif; ?>
          
          <!-- Photo Upload -->
          <div class="flex justify-center">
            <div>
              <label for="photo" class="block text-sm font-medium text-gray-700 text-center mb-2">
                Student Photo <?= !$isEditMode ? '<span class="text-red-500">*</span>' : '' ?>
              </label>
              <input type="file" name="photo" id="photo" accept="image/*" <?= !$isEditMode ? 'required' : '' ?>
                  class="hidden" onchange="previewImage(this, 'photoPreview')">
              
              <label for="photo" class="cursor-pointer">
                  <div id="photoPreview" class="image-upload-container">
                      <div class="image-upload-label">
                          <i class="fas fa-camera image-upload-icon"></i>
                          <p class="text-xs">Click to <?= $isEditMode && $existingPhotoPath ? 'change' : 'upload' ?></p>
                          <p class="text-xxs mt-1">JPEG/PNG, Max 2MB</p>
                      </div>
                  </div>
              </label>
            </div>
          </div>

          <!-- Personal Information -->
          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
              <i class="fas fa-id-card text-indigo-500 mr-2"></i>
              Personal Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="fullName" class="block text-sm font-medium text-gray-700">
                  Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fullName" id="fullName" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="Enter full name" 
                    value="<?= htmlspecialchars($_SESSION['form_data']['fullName'] ?? $userData['full_name'] ?? '') ?>">
              </div>
              
              <div>
                <label for="fatherName" class="block text-sm font-medium text-gray-700">
                  Father's Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fatherName" id="fatherName" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="Enter father's name"
                    value="<?= htmlspecialchars($_SESSION['form_data']['fatherName'] ?? '') ?>">
              </div>
              
              <div>
                <label for="bForm" class="block text-sm font-medium text-gray-700">
                  B-form/CNIC <span class="text-red-500">*</span>
                </label>
                <input type="text" name="bForm" id="bForm" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 cnic-input"
                    placeholder="XXXXX-XXXXXXX-X" 
                    value="<?= htmlspecialchars($_SESSION['form_data']['bForm'] ?? $userData['cnic'] ?? '') ?>"
                    oninput="formatCNIC(this)">
              </div>
              
              <div>
                <label for="fatherCNIC" class="block text-sm font-medium text-gray-700">
                  Father's CNIC <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fatherCNIC" id="fatherCNIC" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 cnic-input"
                    placeholder="XXXXX-XXXXXXX-X" 
                    value="<?= htmlspecialchars($_SESSION['form_data']['fatherCNIC'] ?? '') ?>"
                    oninput="formatCNIC(this)">
              </div>
              
              <div>
                <label for="dob" class="block text-sm font-medium text-gray-700">
                  Date of Birth <span class="text-red-500">*</span>
                </label>
                <input type="date" name="dob" id="dob" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    value="<?= htmlspecialchars($_SESSION['form_data']['dob'] ?? '') ?>">
              </div>
              
              <div>
                <label for="guardianOccupation" class="block text-sm font-medium text-gray-700">
                  Guardian Occupation <span class="text-red-500">*</span>
                </label>
                <input type="text" name="guardianOccupation" id="guardianOccupation" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="Occupation"
                    value="<?= htmlspecialchars($_SESSION['form_data']['guardianOccupation'] ?? '') ?>">
              </div>
            </div>
          </div>
          
          <!-- Address Information -->
          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
              <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
              Address Information
            </h3>
            
            <div>
              <label for="postalAddress" class="block text-sm font-medium text-gray-700">
                Postal Address <span class="text-red-500">*</span>
              </label>
              <textarea name="postalAddress" id="postalAddress" rows="3" required
                  class="mt-1 block p-4 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                  placeholder="Full postal address"><?= htmlspecialchars($_SESSION['form_data']['postalAddress'] ?? '') ?></textarea>
            </div>
          </div>
          
          <!-- Education Information -->
          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
              <i class="fas fa-graduation-cap text-indigo-500 mr-2"></i>
              Education Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="lastSchool" class="block text-sm font-medium text-gray-700">
                  Last Attended School <span class="text-red-500">*</span>
                </label>
                <input type="text" name="lastSchool" id="lastSchool" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="School name"
                    value="<?= htmlspecialchars($_SESSION['form_data']['lastSchool'] ?? '') ?>">
              </div>
              
              <div>
                <label for="gradeMarks" class="block text-sm font-medium text-gray-700">
                  Obtained Marks <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" name="gradeMarks" id="gradeMarks" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="Obtained marks"
                    value="<?= htmlspecialchars($_SESSION['form_data']['gradeMarks'] ?? '') ?>">
              </div>
              
              <div>
                <label for="totalMarks" class="block text-sm font-medium text-gray-700">
                  Total Marks <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" name="totalMarks" id="totalMarks" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="Total marks"
                    value="<?= htmlspecialchars($_SESSION['form_data']['totalMarks'] ?? '') ?>">
              </div>
              
              <div>
                <label for="passingDate" class="block text-sm font-medium text-gray-700">
                  Passing Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="passingDate" id="passingDate" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    value="<?= htmlspecialchars($_SESSION['form_data']['passingDate'] ?? '') ?>">
              </div>
            </div>
          </div>
          
          <!-- Contact Information -->
          <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
              <i class="fas fa-phone-alt text-indigo-500 mr-2"></i>
              Contact Information
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="contactNo" class="block text-sm font-medium text-gray-700">
                  Contact No <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="contactNo" id="contactNo" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="03XX-XXXXXXX"
                    value="<?= htmlspecialchars($_SESSION['form_data']['contactNo'] ?? '') ?>"
                    oninput="formatPhone(this)">
              </div>
              
              <div>
                <label for="emergencyContact" class="block text-sm font-medium text-gray-700">
                  Emergency Contact No <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="emergencyContact" id="emergencyContact" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="03XX-XXXXXXX"
                    value="<?= htmlspecialchars($_SESSION['form_data']['emergencyContact'] ?? '') ?>"
                    oninput="formatPhone(this)">
              </div>
              
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                  Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                    placeholder="your@email.com" 
                    value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? $userData['email'] ?? '') ?>">
              </div>
            </div>
          </div>
          
          <!-- Confirmation Section -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200 mb-6">
              <h3 class="text-sm font-medium text-yellow-800 flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                Important Notice
              </h3>
              <p class="text-xs text-yellow-700 mt-1">
                Once submitted, this application cannot be edited. Please verify all information before final submission.
              </p>
            </div>
            
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input id="confirmation" name="confirmation" type="checkbox" required
                  class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                  <?= isset($_SESSION['form_data']['confirmation']) ? 'checked' : '' ?>>
              </div>
              <div class="ml-3 text-sm">
                <label for="confirmation" class="font-medium text-gray-700">
                  I confirm that all information provided is accurate and complete
                  <span class="text-red-500">*</span>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Form Actions -->
          <div class="pt-6 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
            <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-redo mr-2"></i> Reset Form
            </button>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-paper-plane mr-2"></i> <?= $isEditMode ? 'Update Application' : 'Submit Application' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="../partials/script.js"></script>
</body>
</html>
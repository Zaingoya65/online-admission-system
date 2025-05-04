<?php 
include '../session_auth.php'; 
include '../db/db_connection.php';

// Initialize session variables if they don't exist
if (!isset($_SESSION['form_errors'])) {
    $_SESSION['form_errors'] = [];
}
if (!isset($_SESSION['form_data'])) {
    $_SESSION['form_data'] = [];
}

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
        // Handle error
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
    
    // If no errors, process the form
    if (empty($errors)) {
        $photoPath = $existingPhotoPath; // Keep existing photo if no new one uploaded
        
        // Handle file upload only if a new file is provided
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
        } elseif (!$photoPath) {
            // Only require photo if not in edit mode or if no existing photo
            if (!$isEditMode) {
                $errors[] = "Student photo is required";
            }
        }
        
        // If there were file upload errors, store them and redirect back
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
                
                $stmt->execute($data);
                $applicationId = $_GET['edit'];
            } else {
                // Create new application
                $data['submitted_at'] = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("
                    INSERT INTO applications (
                        full_name, father_name, b_form, father_cnic, dob, guardian_occupation,
                        postal_address, last_school, grade_marks, total_marks, passing_date,
                        contact_no, emergency_contact, email, photo_path, user_id, submitted_at
                    ) VALUES (
                        :full_name, :father_name, :b_form, :father_cnic, :dob, :guardian_occupation,
                        :postal_address, :last_school, :grade_marks, :total_marks, :passing_date,
                        :contact_no, :emergency_contact, :email, :photo_path, :user_id, :submitted_at
                    )
                ");
                
                $stmt->execute($data);
                $applicationId = $pdo->lastInsertId();
            }
            
            // Store application ID in session for document upload page
            $_SESSION['current_application_id'] = $applicationId;
            
            // Clear form data from session
            unset($_SESSION['form_errors']);
            unset($_SESSION['form_data']);
            
            // Redirect to success page
            header('Location: application_success.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: applicationform.php' . ($isEditMode ? '?edit=' . $_GET['edit'] : ''));
            exit;
        }
    } else {
        // If there were errors, store them in session and redirect back
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header('Location: applicationform.php' . ($isEditMode ? '?edit=' . $_GET['edit'] : ''));
        exit;
    }
}

// Fetch user data from registeredusers table
$userData = [];
try {
    $stmt = $pdo->prepare("SELECT full_name, cnic, email FROM registeredusers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error or set default empty values
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
  <title><?php echo $isEditMode ? 'Edit' : 'New'; ?> Application - Alhijrah AHRSC</title>
  <style>
    .image-upload-container {
      position: relative;
      width: 100%;
      height: 200px;
      border: 2px dashed #d1d5db;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
    }
    
    .image-upload-container:hover {
      border-color: #818cf8;
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
      font-size: 2rem;
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
      border-radius: 0.375rem;
    }
    
    .image-upload-change {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background: rgba(255, 255, 255, 0.8);
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      color: #4f46e5;
      display: none;
    }
    
    .image-upload-container:hover .image-upload-change {
      display: block;
    }
  </style>
  <script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                  <img src="${e.target.result}" class="image-upload-preview" alt="Preview">
                  <span class="image-upload-change">Change Photo</span>
                `;
            }
            
            reader.readAsDataURL(file);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // If in edit mode and has existing photo, show it
        <?php if ($isEditMode && $existingPhotoPath): ?>
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = `
              <img src="../uploads/applications/<?php echo $existingPhotoPath; ?>" class="image-upload-preview" alt="Preview">
              <span class="image-upload-change">Change Photo</span>
            `;
        <?php endif; ?>
    });
  </script>
</head>
<body class="bg-gray-100">
<!-- Error Checking  -->
<?php if (!empty($_SESSION['form_errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <?php foreach ($_SESSION['form_errors'] as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
  <!-- Mobile menu button -->
  <button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 text-gray-600 p-2">
    <i class="fas fa-bars" id="menuIcon"></i>
  </button>

  <!-- Sidebar Overlay (Mobile only) -->
  <div id="sidebarOverlay" class="sidebar-overlay"></div>

  <div class="flex">
    <!-- Sidebar -->
    <?php include '../partials/sidebar.php'; ?>
    <!-- Main Content Area -->
    <div class="flex-1">
      <!-- Header -->
      <?php include '../partials/header.php'; ?>

      <!-- Content -->
      <div class="p-6 md:p-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <!-- Enhanced Header Section -->
          <div class="border-b border-gray-200 pb-4 mb-6">
            <div class="flex justify-between items-center">
              <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <span class="bg-indigo-100 p-2 rounded-full mr-3">
                  <i class="fas fa-user-graduate text-indigo-600"></i>
                </span>
                <?php echo $isEditMode ? 'Edit Application' : 'New Application'; ?>
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
                <li>For assistance, contact admissions office at <span class="font-semibold">admissions@alhijrah.edu.pk</span></li>
              </ul>
            </div>
          </div>
          
          <form class="space-y-6" action="" method="POST" enctype="multipart/form-data">
            <?php if ($isEditMode): ?>
              <input type="hidden" name="edit_mode" value="1">
            <?php endif; ?>
            
            <!-- Personal Information Card -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-id-card text-indigo-500 mr-2"></i>
                Personal Information
              </h3>
              
              <div>
                <label for="photo" class="block text-sm font-medium text-gray-700">
                  Student Photo <?php if (!$isEditMode): ?><span class="text-red-500">*</span><?php endif; ?>
                </label>
                <div class="mt-1">
                  <input type="file" name="photo" id="photo" accept="image/*" <?php if (!$isEditMode) echo 'required'; ?>
                      class="hidden" onchange="previewImage(this, 'photoPreview')">
                  
                  <label for="photo" class="cursor-pointer">
                      <div id="photoPreview" class="image-upload-container">
                          <div class="image-upload-label">
                              <i class="fas fa-camera image-upload-icon"></i>
                              <p class="text-xs">Click to <?php echo $isEditMode && $existingPhotoPath ? 'change' : 'upload'; ?> photo</p>
                              <p class="text-xxs mt-1">JPEG or PNG (Max 2MB)</p>
                          </div>
                      </div>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <!-- Rest of your form fields remain the same -->
                <div>
                  <label for="fullName" class="block text-sm font-medium text-gray-700">
                    Full Name <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="fullName" id="fullName" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Enter full name" value="<?php echo htmlspecialchars($_SESSION['form_data']['fullName'] ?? $userData['full_name'] ?? ''); ?>">
                  </div>
                </div>
                
                <div>
                  <label for="fatherName" class="block text-sm font-medium text-gray-700">
                    Father's Name <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-male text-gray-400"></i>
                    </div>
                    <input type="text" name="fatherName" id="fatherName" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Enter father's name">
                  </div>
                </div>
                
                <div>
                  <label for="bForm" class="block text-sm font-medium text-gray-700">
                    B-form/CNIC <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-id-card text-gray-400"></i>
                    </div>
                    <input type="text" name="bForm" id="bForm" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="XXXXX-XXXXXXX-X" value="<?php echo htmlspecialchars($_SESSION['form_data']['bForm'] ?? $userData['cnic'] ?? ''); ?>">
                  </div>
                </div>
                
                <div>
                  <label for="fatherCNIC" class="block text-sm font-medium text-gray-700">
                    Father's CNIC <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-id-card text-gray-400"></i>
                    </div>
                    <input type="text" name="fatherCNIC" id="fatherCNIC" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="XXXXXXXXXXXXX" >
                  </div>
                </div>
                
                <div>
                  <label for="dob" class="block text-sm font-medium text-gray-700">
                    Date of Birth <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-calendar-day text-gray-400"></i>
                    </div>
                    <input type="date" name="dob" id="dob" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                       >
                  </div>
                </div>
                
                <div>
                  <label for="guardianOccupation" class="block text-sm font-medium text-gray-700">
                    Guardian Occupation <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-briefcase text-gray-400"></i>
                    </div>
                    <input type="text" name="guardianOccupation" id="guardianOccupation" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Occupation">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Address Information Card -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
                Address Information
              </h3>
              
              <div>
                <label for="postalAddress" class="block text-sm font-medium text-gray-700">
                  Postal Address <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                  <textarea name="postalAddress" id="postalAddress" rows="3" required
                      class="block p-4 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                      placeholder="Full postal address"><?php echo htmlspecialchars($_SESSION['form_data']['postalAddress'] ?? ''); ?></textarea>
                </div>
              </div>
            </div>
            
            <!-- Education Information Card -->
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
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-school text-gray-400"></i>
                    </div>
                    <input type="text" name="lastSchool" id="lastSchool" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="School name">
                  </div>
                </div>
                
                <div>
                  <label for="gradeMarks" class="block text-sm font-medium text-gray-700">
                    Grade 6th Obtain Marks <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-percentage text-gray-400"></i>
                    </div>
                    <input type="number" name="gradeMarks" id="gradeMarks" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Obtained marks">
                  </div>
                </div>
                
                <div>
                  <label for="totalMarks" class="block text-sm font-medium text-gray-700">
                    Total Marks <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-percentage text-gray-400"></i>
                    </div>
                    <input type="number" name="totalMarks" id="totalMarks" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Total marks">
                  </div>
                </div>
                
                <div>
                  <label for="passingDate" class="block text-sm font-medium text-gray-700">
                    Passing Date <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-calendar-check text-gray-400"></i>
                    </div>
                    <input type="date" name="passingDate" id="passingDate" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                       >
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Contact Information Card -->
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
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-mobile-alt text-gray-400"></i>
                    </div>
                    <input type="tel" name="contactNo" id="contactNo" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="03XXXXXXXXX">
                  </div>
                </div>
                
                <div>
                  <label for="emergencyContact" class="block text-sm font-medium text-gray-700">
                    Emergency Contact No <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-phone text-gray-400"></i>
                    </div>
                    <input type="tel" name="emergencyContact" id="emergencyContact" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="03XXXXXXXXX">
                  </div>
                </div>
                
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" id="email" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="your@email.com" value="<?php echo htmlspecialchars($_SESSION['form_data']['email'] ?? $userData['email'] ?? ''); ?>">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Final Confirmation Section -->
            <div class="mt-8 border-t border-gray-200 pt-6">
              <div class="bg-yellow-50 p-4 rounded-md border border-yellow-200 mb-6">
                <h3 class="text-sm font-medium text-yellow-800 flex items-center">
                  <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                  Important Notice
                </h3>
                <p class="text-xs text-yellow-700 mt-1">
                  Once submitted, this application cannot be edited. Please verify all information before final submission.
                  You will receive a confirmation email with further instructions after submission.
                </p>
              </div>
              
              <div class="flex items-start">
                <div class="flex items-center h-5">
                  <input id="confirmation" name="confirmation" type="checkbox" required
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                    <?php echo isset($_SESSION['form_data']['confirmation']) ? 'checked' : ''; ?>>
                </div>
                <div class="ml-3 text-sm">
                  <label for="confirmation" class="font-medium text-gray-700">
                    I confirm that all information provided is accurate and complete
                    <span class="text-red-500">*</span>
                  </label>
                  <p class="text-xs text-gray-500 mt-1">
                    By checking this box, I certify that all details entered are correct to the best of my knowledge.
                    I understand that providing false information may result in cancellation of admission.
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Post-Submission Instructions -->
            <div class="mt-8 bg-gray-50 p-4 rounded-md border border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 flex items-center">
                <i class="fas fa-clipboard-check text-indigo-500 mr-2"></i>
                After Submission
              </h3>
              <ol class="text-xs text-gray-600 mt-2 list-decimal list-inside space-y-1">
                <li>You will receive a confirmation email with your application reference number</li>
                <li>Print the application form for your records (available in your dashboard)</li>
                <li>You will be redirected to the document upload page after successful submission</li>
                <li>Check your application status regularly in the dashboard</li>
              </ol>
              <p class="text-xs text-gray-500 mt-3">
                <i class="fas fa-clock mr-1"></i> Incomplete Applications considered as rejected
              </p>
            </div>
            
            <!-- Form Actions -->
            <div class="pt-6 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
              <button type="reset" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-redo mr-2"></i> Reset Form
              </button>
              <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-paper-plane mr-2"></i> <?php echo $isEditMode ? 'Update Application' : 'Submit Application'; ?>
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
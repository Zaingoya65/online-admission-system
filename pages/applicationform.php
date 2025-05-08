<?php
session_start();
require_once '../config/db.php'; // Assuming you have a database connection file

$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$applicationData = null;
$isUpdate = false;

// Check if application already exists for this user
$stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ?");
$stmt->execute([$user_id]);
$applicationData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($applicationData) {
    $isUpdate = true;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $fatherName = filter_input(INPUT_POST, 'fatherName', FILTER_SANITIZE_STRING);
    $bForm = filter_input(INPUT_POST, 'bForm', FILTER_SANITIZE_STRING);
    $fatherCNIC = filter_input(INPUT_POST, 'fatherCNIC', FILTER_SANITIZE_STRING);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
    $guardianOccupation = filter_input(INPUT_POST, 'guardianOccupation', FILTER_SANITIZE_STRING);
    $postalAddress = filter_input(INPUT_POST, 'postalAddress', FILTER_SANITIZE_STRING);
    $lastSchool = filter_input(INPUT_POST, 'lastSchool', FILTER_SANITIZE_STRING);
    $gradeMarks = filter_input(INPUT_POST, 'gradeMarks', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $totalMarks = filter_input(INPUT_POST, 'totalMarks', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $passingDate = filter_input(INPUT_POST, 'passingDate', FILTER_SANITIZE_STRING);
    $contactNo = filter_input(INPUT_POST, 'contactNo', FILTER_SANITIZE_STRING);
    $emergencyContact = filter_input(INPUT_POST, 'emergencyContact', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validate required fields
    if (!$fullName || !$fatherName || !$bForm || !$fatherCNIC || !$dob || !$guardianOccupation || 
        !$postalAddress || !$lastSchool || !$gradeMarks || !$totalMarks || !$passingDate || 
        !$contactNo || !$emergencyContact || !$email) {
        $error = "Please fill all required fields";
    } else {
        try {
            if ($isUpdate) {
                // Update existing application
                $stmt = $pdo->prepare("UPDATE applications SET 
                    full_name = ?, father_name = ?, b_form = ?, father_cnic = ?, 
                    dob = ?, guardian_occupation = ?, postal_address = ?, last_school = ?, 
                    grade_marks = ?, total_marks = ?, passing_date = ?, contact_no = ?, 
                    emergency_contact = ?, email = ?, updated_at = NOW() 
                    WHERE user_id = ?");
                
                $stmt->execute([
                    $fullName, $fatherName, $bForm, $fatherCNIC, $dob, $guardianOccupation,
                    $postalAddress, $lastSchool, $gradeMarks, $totalMarks, $passingDate,
                    $contactNo, $emergencyContact, $email, $user_id
                ]);
                
                $success = "Application updated successfully!";
            } else {
                // Check if email already exists in applications table
                $stmt = $pdo->prepare("SELECT id FROM applications WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error = "An application with this email already exists.";
                } else {
                    // Insert new application
                    $stmt = $pdo->prepare("INSERT INTO applications (
                        user_id, full_name, father_name, b_form, father_cnic, dob, 
                        guardian_occupation, postal_address, last_school, grade_marks, 
                        total_marks, passing_date, contact_no, emergency_contact, email
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $user_id, $fullName, $fatherName, $bForm, $fatherCNIC, $dob, 
                        $guardianOccupation, $postalAddress, $lastSchool, $gradeMarks, 
                        $totalMarks, $passingDate, $contactNo, $emergencyContact, $email
                    ]);
                    
                    $success = "Application submitted successfully!";
                    $isUpdate = true; // Now it's an update for future submissions
                    
                    // Refresh application data
                    $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $applicationData = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Application Form - Alhijrah AHRSC</title>
</head>
<body class="bg-gray-100">
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
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
              <span class="bg-indigo-100 p-2 rounded-full mr-3">
                <i class="fas fa-user-graduate text-indigo-600"></i>
              </span>
              Student Information
            </h2>
            <div class="bg-blue-50 p-3 rounded-md mt-3">
              <h3 class="text-sm font-medium text-blue-800 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Important Instructions
              </h3>
              <ul class="text-xs text-blue-700 mt-1 list-disc list-inside space-y-1">
                <li>All fields marked with <span class="text-red-500">*</span> are mandatory</li>
                <li>Please fill the form carefully with accurate information</li>
                <li>You will need to upload supporting documents after form submission</li>
                <li>Incomplete or incorrect applications may be rejected</li>
                <li>For assistance, contact admissions office at <span class="font-semibold">admissions@alhijrah.edu.pk</span></li>
              </ul>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($success)): ?>
              <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $success; ?></span>
              </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
              <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
              </div>
            <?php endif; ?>
          </div>
          
          <form class="space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <!-- Personal Information Card -->
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
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="fullName" id="fullName" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Enter full name"
                        value="<?php echo isset($applicationData['full_name']) ? htmlspecialchars($applicationData['full_name']) : ''; ?>">
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
                        placeholder="Enter father's name"
                        value="<?php echo isset($applicationData['father_name']) ? htmlspecialchars($applicationData['father_name']) : ''; ?>">
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
                        placeholder="XXXXX-XXXXXXX-X"
                        value="<?php echo isset($applicationData['b_form']) ? htmlspecialchars($applicationData['b_form']) : ''; ?>">
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
                        placeholder="XXXXX-XXXXXXX-X"
                        value="<?php echo isset($applicationData['father_cnic']) ? htmlspecialchars($applicationData['father_cnic']) : ''; ?>">
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
                        value="<?php echo isset($applicationData['dob']) ? htmlspecialchars($applicationData['dob']) : ''; ?>">
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
                        placeholder="Occupation"
                        value="<?php echo isset($applicationData['guardian_occupation']) ? htmlspecialchars($applicationData['guardian_occupation']) : ''; ?>">
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
                      placeholder="Full postal address"><?php echo isset($applicationData['postal_address']) ? htmlspecialchars($applicationData['postal_address']) : ''; ?></textarea>
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
                        placeholder="School name"
                        value="<?php echo isset($applicationData['last_school']) ? htmlspecialchars($applicationData['last_school']) : ''; ?>">
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
                    <input type="number" name="gradeMarks" id="gradeMarks" step="0.01" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Obtained marks"
                        value="<?php echo isset($applicationData['grade_marks']) ? htmlspecialchars($applicationData['grade_marks']) : ''; ?>">
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
                    <input type="number" name="totalMarks" id="totalMarks" step="0.01" required
                        class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                        placeholder="Total marks"
                        value="<?php echo isset($applicationData['total_marks']) ? htmlspecialchars($applicationData['total_marks']) : ''; ?>">
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
                        value="<?php echo isset($applicationData['passing_date']) ? htmlspecialchars($applicationData['passing_date']) : ''; ?>">
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
                        placeholder="03XX-XXXXXXX"
                        value="<?php echo isset($applicationData['contact_no']) ? htmlspecialchars($applicationData['contact_no']) : ''; ?>">
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
                        placeholder="03XX-XXXXXXX"
                        value="<?php echo isset($applicationData['emergency_contact']) ? htmlspecialchars($applicationData['emergency_contact']) : ''; ?>">
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
                        placeholder="your@email.com"
                        value="<?php echo isset($applicationData['email']) ? htmlspecialchars($applicationData['email']) : (isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''); ?>">
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
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
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
                <li>Upload required documents in the 'Document Upload' section within 3 days</li>
                <li>Pay the application fee</li>
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
                <i class="fas fa-paper-plane mr-2"></i> <?php echo $isUpdate ? 'Update Application' : 'Submit Application'; ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toggle sidebar on mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const menuIcon = document.getElementById('menuIcon');
    const collapseToggle = document.getElementById('collapseToggle');
    
    // Mobile toggle
    sidebarToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
      
      // Toggle between hamburger and close icon
      if (sidebar.classList.contains('active')) {
        menuIcon.classList.replace('fa-bars', 'fa-times');
      } else {
        menuIcon.classList.replace('fa-times', 'fa-bars');
      }
    });
    
    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      sidebarOverlay.classList.remove('active');
      menuIcon.classList.replace('fa-times', 'fa-bars');
    });
    
    // Desktop collapse/expand
    collapseToggle.addEventListener('click', function() {
      sidebar.classList.toggle('collapsed');
    });
  </script>
</body>
</html>
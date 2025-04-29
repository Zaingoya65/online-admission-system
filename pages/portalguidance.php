<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Portal Guidance - Alhijrah AHRSC</title>
  
</head>
<body class="bg-white">
  <!-- Mobile menu button -->
  <button id="sidebarToggle" class="md:hidden fixed top-4 right-0 z-50 text-gray-600 p-2">
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
      <!-- Page Header -->
      <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Admission Portal Guide</h2>
        <p class="text-gray-500 max-w-2xl mx-auto">Step-by-step instructions for completing your admission process through our online portal</p>
      </div>
      
  
      
      <!--  Guidance Sections -->
      <div class=" mb-8">
        <!-- How to Apply Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 guide-section">
          <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-file-signature text-green-500 mr-2"></i> How to Apply
          </h3>
          <ol class="list-decimal pl-5 space-y-4 text-gray-600">
            <li>
              <span class="font-medium text-gray-700">Register your account:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Click "Register" button on portal homepage</li>
                <li>Provide valid email and mobile number</li>
                <li>Create strong password (8+ characters with numbers)</li>
                <li>Verify email through confirmation link</li>
              </ul>
            </li>
            <li>
              <span class="font-medium text-gray-700">Login to your account:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Use registered email and password</li>
                <li>Complete profile setup if first login</li>
              </ul>
            </li>
            <li>
              <span class="font-medium text-gray-700">Fill application form:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Navigate to "Application Form" section</li>
                <li>Complete all required fields (marked with *)</li>
                <li>Save progress regularly</li>
                <li>Review before final submission</li>
              </ul>
            </li>

            <li>
              <span class="font-medium text-gray-700">Pay Application Processing: Fee</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
              <li>Print the challan</li>
              <li>Pay NBP of any Branch</li>
              <li>Upload orginal Challan</li>
              </ul>
            </li>

            <li>
              <span class="font-medium text-gray-700">Upload documents:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Scan documents in clear, readable format</li>
                <li>Acceptable formats: PDF, JPG, PNG</li>
                <li>Maximum file size: 1MB per document</li>
                <li>Required documents:
                  <ul class="list-circle pl-5 mt-1 space-y-1">
                    <li>B-form/CNIC of student</li>
                    <li>Guardian CNIC</li>
                    <li>Last school result card</li>
                    <li>Domicile certificate</li>
                    <li>Income certificate</li>
                    <li>Orginal Challan</li>
                  </ul>
                </li>
              </ul>
            </li>
          </ol>
        </div>
        

        <br>

        <!-- Roll No Slip Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 guide-section " >
          <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-ticket-alt text-purple-500 mr-2"></i> Roll No Slip Download
          </h3>
          <ol class="list-decimal pl-5 space-y-4 text-gray-600">
            <li>
              <span class="font-medium text-gray-700">When available:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>7 days before admission test date</li>
                <li>After application approval </li>
                <li>After complete fee payment verification</li>
              </ul>
            </li>
            <li>
              <span class="font-medium text-gray-700">How to download:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Login to your portal account</li>
                <li>Go to "Application Status" section</li>
                <li>Click "Download Roll No Slip" button</li>
                <li>Save PDF file and print 2 copies</li>
              </ul>
            </li>
            <li>
              <span class="font-medium text-gray-700">Information on slip:</span>
              <ul class="list-disc pl-5 mt-1 space-y-1">
                <li>Student name and photo</li>
                <li>Application number</li>
                <li>Test date and time</li>
                <li>Test center address</li>
                <li>Required items to bring</li>
                <li>Important instructions</li>
              </ul>
            </li>
           
          </ol>
        </div>
      </div>
      
     
      <!-- Help Section -->
      <div class="bg-blue-50 border border-blue-100 rounded-lg p-6">
        <h3 class="text-xl font-semibold text-blue-800 mb-4 flex items-center">
          <i class="fas fa-question-circle text-blue-500 mr-2"></i> Need Further Assistance?
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center mb-2">
              <div class="bg-blue-100 p-2 rounded-full mr-3">
                <i class="fas fa-envelope text-blue-600"></i>
              </div>
              <h4 class="font-medium text-gray-800">Email Support</h4>
            </div>
            <p class="text-sm text-gray-600 mb-2">Send your queries to our support team</p>
            <a href="mailto:admissions@alhijrah.edu.pk" class="text-blue-600 text-sm font-medium">admissions@alhijrah.edu.pk</a>
          </div>
          <div class="bg-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center mb-2">
              <div class="bg-blue-100 p-2 rounded-full mr-3">
                <i class="fas fa-phone-alt text-blue-600"></i>
              </div>
              <h4 class="font-medium text-gray-800">Helpline</h4>
            </div>
            <p class="text-sm text-gray-600 mb-2">Call during office hours (9AM-5PM)</p>
            <a href="tel:+921112345678" class="text-blue-600 text-sm font-medium">+92 111 234 5678</a>
          </div>
          <div class="bg-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center mb-2">
              <div class="bg-blue-100 p-2 rounded-full mr-3">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
              </div>
              <h4 class="font-medium text-gray-800">Visit Campus</h4>
            </div>
            <p class="text-sm text-gray-600 mb-2">Get in-person assistance</p>
            <a href="#" class="text-blue-600 text-sm font-medium">View Campus Map</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../partials/script.js"></script>
</body>
</html>
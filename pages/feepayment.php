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
  <title>Fee Payment - Alhijrah AHRSC</title>
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
        <div class="">
          <!-- Payment Summary -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-file-invoice-dollar text-indigo-500 mr-2"></i>
              Fee Challan
            </h2>
            
            <div class="bg-blue-50 p-4 rounded-md border border-blue-200 mb-6">
              <h3 class="text-sm font-medium text-blue-800 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Payment Instructions
              </h3>
              <ol class="text-xs text-blue-700 mt-2 list-decimal list-inside space-y-1">
                <li>Download the challan form below</li>
                <li>Print the challan on A4 size paper</li>
                <li>Visit any branch of the designated bank</li>
                <li>Submit the payment with the printed challan</li>
                <li>Keep the bank receipt for your records</li>
              </ol>
            </div>
            





             <!-- Important Notes -->
             <div class="bg-blue-50 p-4 rounded-md border border-blue-200 mb-6">
            <h2 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
              <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
              Important Notes
            </h2>
            <ul class="text-xs text-gray-600 space-y-2">
              <li class="flex items-start">
                <i class="fas fa-circle text-yellow-500 text-2xs mr-2 mt-1"></i>
                <span>The challan is valid for 15 days from the date of generation</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-circle text-yellow-500 text-2xs mr-2 mt-1"></i>
                <span>Late payment not accepted </span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-circle text-yellow-500 text-2xs mr-2 mt-1"></i>
                <span>For any issues with challan generation, contact accounts department</span>
              </li>
            
            </ul>
          </div>





            <!-- Challan Information -->
            <div class="challan-box p-6 rounded-lg mb-6 text-center">
              <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-file-pdf text-indigo-600 text-2xl"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-800 mb-1">Admission Fee Challan</h3>
              <p class="text-sm text-gray-600 mb-4">Valid until: 30 June 2025</p>
              
              <div class="max-w-md mx-auto bg-white rounded-lg p-4 shadow-xs mb-4">
                <div class="flex justify-between py-2 border-b">
                  <span class="text-sm text-gray-600">Application No:</span>
                  <span class="text-sm font-medium">AHRSC-2025-1234</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                  <span class="text-sm text-gray-600">Student Name:</span>
                  <span class="text-sm font-medium">Zain Ul Abideen</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                  <span class="text-sm text-gray-600">Amount Payable:</span>
                  <span class="text-sm font-medium text-indigo-600">Rs. 250</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-sm text-gray-600">Bank Name:</span>
                  <span class="text-sm font-medium">National Bank Of Pakistan(NBP)</span>
                </div>
              </div>
              
              <button id="downloadChallan" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <i class="fas fa-download mr-2"></i> Download Challan
              </button>
            </div>
            
         
          
         
        </div>
      </div>
    </div>
  </div>

  <script src="../partials/script.js"></script>
</body>
</html>
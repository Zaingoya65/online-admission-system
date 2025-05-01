<?php include 'session_auth.php'; ?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Dashboard - Alhijrah AHRSC</title>
  
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
    <div id="sidebar" class="sidebar bg-white min-h-screen shadow-lg">
      
      
      
      <?php 
      include '../partials/sidebar.php';
      ?>
      
    </div>

    <!-- Main Content Area -->
    <div class="flex-1">
      <!-- Header -->
      <?php include '../partials/header.php'; ?>

      <!-- Content -->
      <div class="p-6 md:p-8">
        <!-- Your main content here -->
          <!-- Content -->
      <div class="p-6 md:p-8">
        <!-- Page Header -->
        <div class="mb-8 text-center">
          <h2 class="text-2xl font-bold text-blue-800 mb-2">Admission Eligibility Requirements</h2>
          <p class="text-gray-600">Please review all criteria carefully before applying</p>
        </div>
        
        <!-- Criteria Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Previous Institute Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-blue-500">
            <div class="flex items-center mb-4">
              <div class="bg-blue-100 p-3 rounded-full mr-4">
                <i class="fas fa-school text-blue-600 text-lg"></i>
              </div>
              <h3 class="text-lg font-semibold">Previous Institute</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>Must have attended last government institute</li>
              <li>Semi-government schools not eligible</li>
              <li>Private schools not eligible</li>
              <li>Madrasah students eligible if registered with government</li>
            </ul>
          </div>
          
          <!-- Academic Performance Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-green-500">
            <div class="flex items-center mb-4">
              <div class="bg-green-100 p-3 rounded-full mr-4">
                <i class="fas fa-award text-green-600 text-xl"></i>
              </div>
              <h3 class="text-lg font-semibold">Academic Performance</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>Minimum 75% marks in last attended grade</li>
              <li>No failing grades in any subject</li>
              <li>Certified transcripts required</li>
              <li>Special consideration for extracurricular achievements</li>
            </ul>
          </div>
          
          <!-- Financial Background Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-purple-500">
            <div class="flex items-center mb-4">
              <div class="bg-purple-100 p-3 rounded-full mr-4">
                <i class="fas fa-money-bill-wave text-purple-600 text-xl"></i>
              </div>
              <h3 class="text-lg font-semibold">Financial Background</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>Guardian's annual income must be less than 5 lac PKR</li>
              <li>Income certificate from employer or revenue department required</li>
              <li>Bank statements may be requested for verification</li>
              <li>Special cases reviewed by committee</li>
            </ul>
          </div>
          
          <!-- Domicile Requirements Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-yellow-500">
            <div class="flex items-center mb-4">
              <div class="bg-yellow-100 p-3 rounded-full mr-4">
                <i class="fas fa-map-marker-alt text-yellow-600 text-xl"></i>
              </div>
              <h3 class="text-lg font-semibold">Domicile Requirements</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>Must have domicile of Balochistan, Sindh, or South Punjab</li>
              <li>Original domicile certificate required</li>
              <li>Special quota, having Ziarat or Dera Gazi Khan Domicile</li>
            </ul>
          </div>
          
          <!-- Age Limit Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-red-500">
            <div class="flex items-center mb-4">
              <div class="bg-red-100 p-3 rounded-full mr-4">
                <i class="fas fa-birthday-cake text-red-600 text-xl"></i>
              </div>
              <h3 class="text-lg font-semibold">Age Limit</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>11-12 years</li>
              <li>Maximum age relaxation: 3 months</li>
            </ul>
          </div>
          
          <!-- Additional Requirements Card -->
          <div class="criteria-card bg-white rounded-xl shadow-md overflow-hidden p-6 border-l-4 border-indigo-500">
            <div class="flex items-center mb-4">
              <div class="bg-indigo-100 p-3 rounded-full mr-4">
                <i class="fas fa-clipboard-check text-indigo-600 text-xl"></i>
              </div>
              <h3 class="text-lg font-semibold">Documents Requirements</h3>
            </div>
            <ul class="list-disc pl-5 space-y-2 text-gray-700">
              <li>Medical fitness certificate</li>
              <li>Character certificate from previous institute</li>
              <li>B-form/CNIC of student and guardian</li>
            
            </ul>
          </div>
        </div>
        
        <!-- Important Notes Section -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="text-xl font-semibold text-blue-800 mb-4 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i> Important Notes
          </h3>
          <ul class="list-disc pl-5 space-y-2 text-blue-900">
            <li>False information will lead to immediate disqualification</li>
            <li>Admission test and interview are mandatory</li>
            <li>Selected candidates must complete formalities within 7 days</li>
          </ul>
        </div>
        
<br>

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
        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
          <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300">
            <i class="fas fa-file-alt mr-2"></i> Download Criteria PDF
          </button>
          <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300">
            <i class="fas fa-check-circle mr-2"></i> I Meet All Requirements
          </button>
         
        </div>
      </div>
    </div>
  </div>
      </div>
    </div>
  </div>

 <script src="../partials/script.js"></script>
</body>
</html>
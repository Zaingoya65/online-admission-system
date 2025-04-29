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
  <title>Application Status - Alhijrah AHRSC</title>
 
</head>
<body class="bg-gray-50">
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
      <div class="p-6">
        <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-xs border">
          <!-- Table Header -->
          <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Your Applications</h2>
            <p class="text-sm text-gray-600">Track all your submitted applications</p>
          </div>
          
          <!-- Applications Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Application #
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Applied On
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Campus
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Class/Grade
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <!-- Current Application -->
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-600">AHRSC-2023-1245</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">15 June 2023</div>
                    <div class="text-xs text-gray-500">10:45 AM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Main Campus</div>
                    <div class="text-xs text-gray-500">Islamabad</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Grade 7</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge status-pending">
                      <i class="fas fa-spinner fa-spin mr-1"></i> Under Review
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900 mr-4 download-btn" data-app-id="1245">
                      <i class="fas fa-file-pdf mr-1"></i> Download
                    </button>
                    <a href="#" class="text-gray-600 hover:text-gray-900">
                      <i class="fas fa-eye mr-1"></i> View
                    </a>
                  </td>
                </tr>
                
                <!-- Previous Application (Example) -->
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-600">AHRSC-2022-0987</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">05 May 2022</div>
                    <div class="text-xs text-gray-500">02:30 PM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Main Campus</div>
                    <div class="text-xs text-gray-500">Islamabad</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Grade 6</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge status-approved">
                      <i class="fas fa-check-circle mr-1"></i> Approved
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900 mr-4 download-btn" data-app-id="0987">
                      <i class="fas fa-file-pdf mr-1"></i> Download
                    </button>
                    <a href="#" class="text-gray-600 hover:text-gray-900">
                      <i class="fas fa-eye mr-1"></i> View
                    </a>
                  </td>
                </tr>
                
                <!-- Another Previous Application (Example) -->
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-600">AHRSC-2021-0765</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">12 April 2021</div>
                    <div class="text-xs text-gray-500">11:15 AM</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Branch Campus</div>
                    <div class="text-xs text-gray-500">Rawalpindi</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">Grade 5</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge status-rejected">
                      <i class="fas fa-times-circle mr-1"></i> Rejected
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button class="text-blue-600 hover:text-blue-900 mr-4 download-btn" data-app-id="0765">
                      <i class="fas fa-file-pdf mr-1"></i> Download
                    </button>
                    <a href="#" class="text-gray-600 hover:text-gray-900">
                      <i class="fas fa-eye mr-1"></i> View
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Additional Information -->
          <div class="p-4 border-t bg-gray-50">
            <h3 class="text-sm font-medium text-gray-800 mb-2">Application Status Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
              <div class="flex items-center">
                <span class="status-badge status-pending mr-2">Under Review</span>
                <span class="text-gray-600">Application is being processed</span>
              </div>
              <div class="flex items-center">
                <span class="status-badge status-approved mr-2">Approved</span>
                <span class="text-gray-600">Application was successful</span>
              </div>
              <div class="flex items-center">
                <span class="status-badge status-rejected mr-2">Rejected</span>
                <span class="text-gray-600">Application was not accepted</span>
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
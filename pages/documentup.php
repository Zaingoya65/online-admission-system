<?php include '../session_auth.php'; ?>

<!doctype html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Document Upload - Alhijrah AHRSC</title>
  
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
        <div class="">
          <!-- Upload Summary -->
          <div class="bg-white rounded-lg shadow-xs border p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-medium text-gray-800">
                <i class="fas fa-file-upload text-blue-500 mr-2"></i>
                Required Documents
              </h2>
              <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">
                <span id="uploadedCount">0</span> of 6 uploaded
              </span>
            </div>
            
            <p class="text-sm text-gray-600 mb-6">
              Please upload clear scans of the following documents. All fields marked with <span class="text-red-500">*</span> are mandatory.
            </p>
            
            <!-- Document List -->
            <div class="space-y-4">
              <!-- Passport Photo -->
              <div class="upload-item p-4 bg-white rounded border" id="passportItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Passport Photo <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">35×45mm with white background</p>
                    <div id="passportFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="passportUpload" class="file-input" accept="image/*" required>
                    <label for="passportUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="passportAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Form-B -->
              <div class="upload-item p-4 bg-white rounded border" id="formBItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Form-B <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">NADRA issued </p>
                    <div id="formBFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="formBUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="formBUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="formBAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Domicile Certificate -->
              <div class="upload-item p-4 bg-white rounded border" id="domicileItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Domicile Certificate <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Issued by competent authority</p>
                    <div id="domicileFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="domicileUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="domicileUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="domicileAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Guardian CNIC -->
              <div class="upload-item p-4 bg-white rounded border" id="guardianItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Guardian CNIC <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Front and back in single file</p>
                    <div id="guardianFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="guardianUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="guardianUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="guardianAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Income Certificate -->
              <div class="upload-item p-4 bg-white rounded border" id="incomeItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Income Certificate <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Or latest salary slip</p>
                    <div id="incomeFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="incomeUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="incomeUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="incomeAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Grade 6 Result -->
              <div class="upload-item p-4 bg-white rounded border" id="resultItem">
                <div class="flex items-start justify-between">
                  <div>
                    <h3 class="font-medium text-gray-800 flex items-center">
                      Grade 6 Result <span class="text-red-500 ml-1">*</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Official result card</p>
                    <div id="resultFile" class="text-xs text-blue-600 mt-2 hidden"></div>
                  </div>
                  <div>
                    <input type="file" id="resultUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="resultUpload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                      <i class="fas fa-cloud-upload-alt mr-2"></i>
                      <span id="resultAction">Upload</span>
                    </label>
                  </div>
                </div>
              </div>

                <!-- Challan -->
                <div class="upload-item p-4 bg-white rounded border" id="resultItem">
                    <div class="flex items-start justify-between">
                      <div>
                        <h3 class="font-medium text-gray-800 flex items-center">
                            Challan <span class="text-red-500 ml-1">*</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Challan receipt</p>
                        <div id="challan" class="text-xs text-blue-600 mt-2 hidden"></div>
                      </div>
                      <div>
                        <input type="file" id="resultUpload" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                        <label for="Challanupload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-800 flex items-center">
                          <i class="fas fa-cloud-upload-alt mr-2"></i>
                          <span id="resultAction">Upload</span>
                        </label>
                      </div>
                    </div>
                  </div>

            </div>
          </div>
          
          <!-- Submission Section -->
          <div class="bg-white rounded-lg shadow-xs border p-6">
            <div class="flex items-start mb-6">
              <input type="checkbox" id="declaration" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
              <label for="declaration" class="ml-3 text-sm text-gray-700">
                I certify that all uploaded documents are authentic and belong to me/my ward.
                <span class="text-red-500">*</span>
                <p class="text-xs text-gray-500 mt-1">Providing false documents may result in cancellation of admission.</p>
              </label>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
              <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700">
                <i class="fas fa-check-circle mr-2"></i> Submit Documents
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script src="../partials/script.js"></script>
</body>
</html>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<style>
    .sidebar {
      transition: all 0.3s ease;
      width: 16rem;
    }
    .sidebar.collapsed {
      width: 5rem;
    }
    .sidebar.collapsed .nav-text,
    .sidebar.collapsed .student-name,
    .sidebar.collapsed .student-welcome,
    .sidebar.collapsed .school-name {
      display: none;
    }
    .sidebar.collapsed .logo-container {
      justify-content: center;
      padding: 1rem 0;
    }
    .sidebar.collapsed .logo-img {
      width: 2.5rem;
      height: 2.5rem;
    }
    .sidebar.collapsed .profile-summary {
      justify-content: center;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%;
        top: 0;
        z-index: 50;
        height: 100vh;
      }
      .sidebar.active {
        left: 0;
      }
      .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 40;
      }
      .sidebar-overlay.active {
        display: block;
      }
    }
  </style>



<div id="sidebar" class="sidebar bg-white min-h-screen shadow-lg">
      <div class="p-4 border-b border-gray-200 logo-container">
        <div class="flex items-center justify-center">
          <img src="../assets/images/logo.png" alt="AL-Hijrah logo" class="h-16 logo-img">
        </div>
        <div class="mt-2 text-center text-sm text-gray-600 school-name">
          Al-Hijrah Residential School & College
        </div>
      </div>
      
      <div class="p-4">
        <!-- Student Profile Summary -->
        <div class="flex items-center space-x-3 mb-6 p-2 bg-blue-50 rounded-lg profile-summary">
          <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="fas fa-user text-blue-600"></i>
          </div>
          <div>
            <h4 class="font-medium text-sm student-name">Zain Ul Abideen</h4>
            <p class="text-xs text-gray-500 student-welcome">Welcome! Again</p>
          </div>
        </div>
        
        <!-- Navigation -->
        <nav>
          <ul class="space-y-1">
            <li>
            <a href="/onlineAd/pages/adcriteria.php"
   class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo ($currentPage == 'adcriteria.php') ? 'bg-blue-100 text-blue-600' : ''; ?>">
   <i class="fas fa-home w-5 text-center"></i>
   <span class="nav-text">Admission Criteria</span>
</a>


            </li>
            <li>
              <a href="/onlineAd/pages/portalguidance.php" 
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo $currentPage == 'portalguidance.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                <i class="fas fa-info-circle w-5 text-center"></i>
                <span class="nav-text">Portal Guidance</span>
              </a>
            </li>
            <li>
              <a href="/onlineAd/pages/applicationform.php" 
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo $currentPage == 'applicationform.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span class="nav-text">Application Form</span>
              </a>
            </li>
            <li>
              <a href="/onlineAd/pages/feepayment.php" 
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo $currentPage == 'feepayment.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                <i class="fas fa-money-bill-wave w-5 text-center"></i>
                <span class="nav-text">Fee Payment</span>
              </a>
            </li>
            <li>
              <a href="/onlineAd/pages/documentup.php" 
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo $currentPage == 'documentup.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                <i class="fas fa-upload w-5 text-center"></i>
                <span class="nav-text">Document Upload</span>
              </a>
            </li>
            <li>
              <a href="/onlineAd/pages/appstatus.php" 
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 <?php echo $currentPage == 'appstatus.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                <i class="fas fa-envelope w-5 text-center"></i>
                <span class="nav-text">Application Status</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-question-circle w-5 text-center"></i>
                <span class="nav-text">FAQs</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                <i class="fas fa-phone-alt w-5 text-center"></i>
                <span class="nav-text">Contact Us</span>
              </a>
            </li>
            <li>
              <a href="#" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 text-red-600">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="nav-text">Logout</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
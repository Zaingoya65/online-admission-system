
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



// for student picture
document.getElementById('studentPhoto').addEventListener('change', function(e) {
  const file = e.target.files[0];
  const preview = document.getElementById('photoPreview');
  const uploadIcon = document.getElementById('uploadIcon');
  const errorElement = document.getElementById('photoError');
  
  // Reset previous errors
  errorElement.classList.add('hidden');
  
  if (file) {
    // Check file size (0.5MB = 500KB = 500000 bytes)
    if (file.size > 500000) {
      errorElement.textContent = 'File size must be less than 0.5MB';
      errorElement.classList.remove('hidden');
      e.target.value = ''; // Clear the file input
      return;
    }
    
    // Check file type
    const validTypes = ['image/jpeg', 'image/png'];
    if (!validTypes.includes(file.type)) {
      errorElement.textContent = 'Only JPG or PNG files are allowed';
      errorElement.classList.remove('hidden');
      e.target.value = ''; // Clear the file input
      return;
    }
    
    // Create preview
    const reader = new FileReader();
    reader.onload = function(event) {
      preview.src = event.target.result;
      preview.classList.remove('hidden');
      uploadIcon.classList.add('hidden');
    }
    reader.readAsDataURL(file);
  } else {
    preview.classList.add('hidden');
    uploadIcon.classList.remove('hidden');
  }
});
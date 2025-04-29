
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

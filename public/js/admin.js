// Sidebar toggle for mobile
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', function() {
    sidebar.classList.toggle('open');
  });
}

// Auto-dismiss flash messages
setTimeout(function() {
  document.querySelectorAll('.alert.fade.show').forEach(function(el) {
    const bsAlert = bootstrap && bootstrap.Alert ? new bootstrap.Alert(el) : null;
    if (bsAlert) bsAlert.close();
    else el.style.display = 'none';
  });
}, 4000);

// Confirm deletes
document.querySelectorAll('[data-confirm]').forEach(function(el) {
  el.addEventListener('click', function(e) {
    if (!confirm(this.dataset.confirm)) e.preventDefault();
  });
});

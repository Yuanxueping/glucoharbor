// Mobile nav
var navToggle = document.getElementById('navToggle');
var navList = document.getElementById('navList');
if (navToggle) {
  navToggle.addEventListener('click', function() {
    navList.classList.toggle('open');
  });
}
// Mobile dropdown
document.querySelectorAll('.has-dropdown > a').forEach(function(a) {
  a.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
      e.preventDefault();
      this.parentElement.classList.toggle('open');
    }
  });
});
// Close on outside click
document.addEventListener('click', function(e) {
  if (navList && navList.classList.contains('open')) {
    if (!navList.contains(e.target) && navToggle && !navToggle.contains(e.target)) {
      navList.classList.remove('open');
    }
  }
});
// Scroll to top
var scrollBtn = document.createElement('button');
scrollBtn.id = 'scrollTop';
scrollBtn.innerHTML = '&#8679;';
scrollBtn.setAttribute('aria-label', 'Back to top');
scrollBtn.style.cssText = 'display:none';
document.body.appendChild(scrollBtn);
window.addEventListener('scroll', function() {
  scrollBtn.style.display = window.scrollY > 400 ? 'flex' : 'none';
});
scrollBtn.addEventListener('click', function() {
  window.scrollTo({top:0,behavior:'smooth'});
});
// Image error fallback
document.querySelectorAll('img').forEach(function(img) {
  img.addEventListener('error', function() { this.style.display = 'none'; });
});

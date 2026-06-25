// Mobile nav toggle
document.getElementById('navToggle').addEventListener('click', function() {
  document.getElementById('navList').classList.toggle('open');
});

// Close nav on outside click
document.addEventListener('click', function(e) {
  const nav = document.getElementById('navList');
  const toggle = document.getElementById('navToggle');
  if (nav && nav.classList.contains('open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
    nav.classList.remove('open');
  }
});

// Smooth scroll to top
const scrollTopBtn = document.createElement('button');
scrollTopBtn.className = 'scroll-top-btn';
scrollTopBtn.innerHTML = '<i class="fas fa-chevron-up"></i>';
scrollTopBtn.setAttribute('aria-label', 'Scroll to top');
scrollTopBtn.style.cssText = 'position:fixed;bottom:24px;right:24px;width:44px;height:44px;background:#1a56db;color:white;border:none;border-radius:50%;cursor:pointer;font-size:16px;box-shadow:0 4px 12px rgba(0,0,0,.2);display:none;align-items:center;justify-content:center;z-index:999;transition:opacity .2s;';
document.body.appendChild(scrollTopBtn);

window.addEventListener('scroll', function() {
  scrollTopBtn.style.display = window.scrollY > 400 ? 'flex' : 'none';
});
scrollTopBtn.addEventListener('click', function() {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Lazy images fallback
document.querySelectorAll('img').forEach(function(img) {
  img.addEventListener('error', function() {
    this.style.display = 'none';
  });
});

/* =====================================================
   相和プラント Webサイト - main.js
   ===================================================== */

document.addEventListener('DOMContentLoaded', function() {
  
  // =====================================================
  // SP Menu (Hamburger Menu)
  // =====================================================
  const menuBtn = document.querySelector('.menu-btn');
  const nav = document.querySelector('.nav');
  const overlay = document.querySelector('.overlay');
  
  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function() {
      menuBtn.classList.toggle('active');
      nav.classList.toggle('active');
      if (overlay) {
        overlay.classList.toggle('active');
      }
      // Prevent body scroll when menu is open
      document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
    });
    
    // Close menu when clicking overlay
    if (overlay) {
      overlay.addEventListener('click', function() {
        menuBtn.classList.remove('active');
        nav.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
      });
    }
    
    // Close menu when clicking nav links
    const navLinks = nav.querySelectorAll('a');
    navLinks.forEach(function(link) {
      link.addEventListener('click', function() {
        menuBtn.classList.remove('active');
        nav.classList.remove('active');
        if (overlay) {
          overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
      });
    });
  }
  
  // =====================================================
  // Tab Switching
  // =====================================================
  const tabNavs = document.querySelectorAll('.tab-nav');
  
  tabNavs.forEach(function(tabNav) {
    const buttons = tabNav.querySelectorAll('button');
    const parent = tabNav.parentElement;
    const contents = parent.querySelectorAll('.tab-content');
    
    buttons.forEach(function(button, index) {
      button.addEventListener('click', function() {
        // Remove active class from all buttons and contents
        buttons.forEach(function(btn) {
          btn.classList.remove('active');
        });
        contents.forEach(function(content) {
          content.classList.remove('active');
        });
        
        // Add active class to clicked button and corresponding content
        button.classList.add('active');
        if (contents[index]) {
          contents[index].classList.add('active');
        }
      });
    });
    
    // Initialize first tab as active
    if (buttons.length > 0) {
      buttons[0].classList.add('active');
    }
    if (contents.length > 0) {
      contents[0].classList.add('active');
    }
  });
  
  // =====================================================
  // Smooth Scroll for Page Top
  // =====================================================
  const pageTopLinks = document.querySelectorAll('a[href="#"]');
  
  pageTopLinks.forEach(function(link) {
    // Only for page-top specific links
    if (link.closest('.page-top')) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }
  });
  
  // =====================================================
  // Current Page Navigation Highlight
  // =====================================================
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navMenuLinks = document.querySelectorAll('.nav-menu a');
  
  navMenuLinks.forEach(function(link) {
    const href = link.getAttribute('href');
    if (href === currentPath) {
      link.classList.add('current');
    }
  });
  
});

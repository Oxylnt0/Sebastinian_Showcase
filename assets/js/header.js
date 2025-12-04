document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.createElement('div');
    toggle.classList.add('mobile-toggle');
    toggle.innerHTML = '<span></span><span></span><span></span>';
    const nav = document.querySelector('nav');
    nav.insertBefore(toggle, nav.querySelector('.nav-links'));

    const navLinks = document.querySelector('.nav-links');
    toggle.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        toggle.classList.toggle('active');
    });

    // Optional: Make SDG dropdown toggle on mobile click
    const sdgDropdown = document.querySelector('.sdg-dropdown');
    sdgDropdown.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.preventDefault(); // Prevent page jump
            sdgDropdown.classList.toggle('active');
        }
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target)) {
            navLinks.classList.remove('show');
            toggle.classList.remove('active');
            sdgDropdown.classList.remove('active');
        }
    });
});

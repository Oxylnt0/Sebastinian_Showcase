document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('nav');
    const navLinks = document.querySelector('.nav-links');
    const toggle = document.querySelector('.menu-toggle');
    const body = document.body;

    // ===== Toggle Mobile Menu =====
    toggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent immediate close
        navLinks.classList.toggle('show');
        toggle.classList.toggle('active');
        body.classList.toggle('no-scroll'); // Prevent background scroll
    });

    // ===== Close menu when clicking outside =====
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target)) {
            navLinks.classList.remove('show');
            toggle.classList.remove('active');
            body.classList.remove('no-scroll');
        }
    });

    // ===== Close menu on link click (mobile only) =====
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                navLinks.classList.remove('show');
                toggle.classList.remove('active');
                body.classList.remove('no-scroll');
            }
        });
    });

    // ===== Add shadow on scroll =====
    const header = document.querySelector('header');
    const scrollShadow = () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };
    window.addEventListener('scroll', scrollShadow);
    scrollShadow(); // Initialize on load

    // ===== Responsive cleanup on window resize =====
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navLinks.classList.remove('show');
            toggle.classList.remove('active');
            body.classList.remove('no-scroll');
        }
    });
});

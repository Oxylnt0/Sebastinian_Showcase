document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 0. SINGLETON LOCK & UTILITIES
    // =========================================================
    // Prevents the script from running multiple times if loaded twice
    if (window.dashboardLoaded) return; 
    window.dashboardLoaded = true;

    const dashboardWrapper = document.querySelector('.dashboard-wrapper');
    const heroPanel = document.querySelector('.dashboard-hero-panel');
    const statsGrid = document.querySelector('.stats-grid');
    const recentList = document.querySelector('.recent-list');
    
    // =========================================================
    // 1. CINEMATIC COUNTER (Animated Stat Cards)
    // =========================================================
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-data .counter');
        const speed = 1500; // Duration in ms

        counters.forEach(counter => {
            const target = +counter.innerText;
            const start = 0;
            const increment = target / (speed / 16); 

            let current = start;
            const updateCount = () => {
                current += increment;
                if (current < target) {
                    counter.innerText = Math.ceil(current);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            // Start the count animation
            updateCount();
        });
    };
    
    // =========================================================
    // 2. PARALLAX AND TILT ENGINE
    // =========================================================
    
    // A. Init 3D Tilt Library
    if (typeof VanillaTilt !== "undefined") {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
            max: 5,
            speed: 1000,
            scale: 1.02,
            glare: true,
            "max-glare": 0.1,
        });
    }

    // B. Floating Orb Parallax
    document.addEventListener('mousemove', (e) => {
        const orbs = document.querySelectorAll('.gold-orb');
        const x = (window.innerWidth - e.pageX * 2) / 100;
        const y = (window.innerHeight - e.pageY * 2) / 100;

        orbs.forEach((orb, index) => {
            const speed = index === 0 ? 1 : -1; // Opposite directions
            orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
        });
    });

    // C. Holographic Mouse Trail (Glow effect on the main panel)
    if (heroPanel) {
        heroPanel.addEventListener('mousemove', (e) => {
            const rect = heroPanel.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Apply a subtle radial background that follows the mouse
            heroPanel.style.background = `
                radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85) 40%)
            `;
            heroPanel.style.transition = 'none'; // Prevent interpolation on background for smooth movement
        });
        
        heroPanel.addEventListener('mouseleave', () => {
             // Reset background to pure glass (smooth fade back)
            heroPanel.style.transition = 'background 0.5s ease';
            heroPanel.style.background = `rgba(255, 255, 255, 0.85)`; 
        });
    }


    // =========================================================
    // 3. WAYPOINT ANIMATIONS (Scroll Reveal)
    // =========================================================
    
    const animateSection = (entry, observer) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');

            // Stagger items if it's the recent list
            if (entry.target.classList.contains('recent-list')) {
                const items = entry.target.querySelectorAll('.recent-item');
                items.forEach((item, index) => {
                    item.style.animationDelay = `${0.1 + index * 0.1}s`;
                    item.classList.add('animate-in');
                });
            }
            
            // Start the counter animation once the stat grid is visible
            if (entry.target.classList.contains('stats-grid')) {
                animateCounters();
            }

            observer.unobserve(entry.target);
        }
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => animateSection(entry, observer));
    }, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });


    // Apply animation classes and observe targets
    if (statsGrid) {
        statsGrid.classList.add('animate-up');
        observer.observe(statsGrid);
    }
    if (recentList) {
        recentList.classList.add('animate-up');
        recentList.querySelectorAll('.recent-item').forEach(item => {
            item.classList.add('animate-up'); // Add entrance animation class to items
        });
        observer.observe(recentList);
    }

    // Append CSS for Waypoint Animations (required since it's JS-driven)
    const styleSheet = document.createElement("style");
    styleSheet.innerText = `
        /* Base hidden state */
        .animate-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        /* Visible state */
        .animate-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Staggered item entrance */
        .recent-item.animate-up {
            opacity: 0;
            transform: translateY(15px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
            animation-fill-mode: both;
        }
        .recent-item.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(styleSheet);
});
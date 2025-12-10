document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 1. HERO PARALLAX (Mouse Movement)
    // Moves the background slightly opposite to mouse for depth
    // =========================================================
    const heroSection = document.querySelector('.hero-section');
    const heroBg = document.querySelector('.hero-bg');

    if (heroSection && heroBg) {
        heroSection.addEventListener('mousemove', (e) => {
            const x = (window.innerWidth - e.pageX * 2) / 90;
            const y = (window.innerHeight - e.pageY * 2) / 90;
            
            heroBg.style.transform = `translateX(${x}px) translateY(${y}px) scale(1.1)`; // Scale 1.1 prevents edges showing
        });
    }

    // =========================================================
    // 2. 3D HOLOGRAPHIC TILT EFFECT
    // Applies a luxury 3D tilt to Cards, Bento Boxes, and Tiles
    // =========================================================
    const tiltElements = document.querySelectorAll('.vision-card, .bento-box, .sdg-tile');

    tiltElements.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left; // Mouse X inside element
            const y = e.clientY - rect.top;  // Mouse Y inside element
            
            // Calculate rotation (Divide by higher number for subtler effect)
            const xRotate = ( (y - rect.height / 2) / rect.height ) * -10; // Max 10deg rotation
            const yRotate = ( (x - rect.width / 2) / rect.width ) * 10;

            // Apply Transform
            card.style.transform = `
                perspective(1000px) 
                rotateX(${xRotate}deg) 
                rotateY(${yRotate}deg) 
                scale(1.02)
            `;
            
            // Optional: Add a "Light Sheen" effect following mouse
            // card.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.2), transparent)`;
        });

        // Reset on mouse leave
        card.addEventListener('mouseleave', () => {
            card.style.transform = `perspective(1000px) rotateX(0) rotateY(0) scale(1)`;
            // card.style.background = ''; // Reset background if used
        });
    });

    // =========================================================
    // 3. INTELLIGENT SCROLL REVEAL (Intersection Observer)
    // Fades elements in as you scroll down
    // =========================================================
    
    // A. Add classes to elements we want to animate
    const targets = document.querySelectorAll('.vision-card, .bento-box, .stat-item, .section-heading, .impact-header');
    targets.forEach((el, index) => {
        el.classList.add('reveal-on-scroll');
        // Add staggered delays for grid items
        if(index % 3 === 0) el.classList.add('delay-100');
        if(index % 3 === 1) el.classList.add('delay-200');
        if(index % 3 === 2) el.classList.add('delay-300');
    });

    // B. The Observer
    const observerOptions = {
        threshold: 0.15, // Trigger when 15% of element is visible
        rootMargin: "0px 0px -50px 0px" // Offset slightly so it triggers before bottom
    };

    const revealOnScroll = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Run once only
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach(el => revealOnScroll.observe(el));

    // =========================================================
    // 4. DYNAMIC NUMBER COUNTER (For Stats)
    // Counts up from 0 to the target number
    // =========================================================
    const statsSection = document.querySelector('.stats-banner');
    const stats = document.querySelectorAll('.stat-item h3');
    let counted = false;

    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !counted) {
                    counted = true;
                    stats.forEach(stat => {
                        const originalText = stat.innerText;
                        const target = parseInt(originalText.replace(/\D/g, '')); // Extract number
                        const suffix = originalText.replace(/[0-9]/g, ''); // Extract %, +, etc.
                        
                        if (isNaN(target)) return; // Skip if no number (e.g., "SDG")

                        let count = 0;
                        const duration = 2000; // 2 seconds
                        const increment = target / (duration / 16); // 60fps

                        const timer = setInterval(() => {
                            count += increment;
                            if (count >= target) {
                                stat.innerText = target + suffix;
                                clearInterval(timer);
                            } else {
                                stat.innerText = Math.floor(count) + suffix;
                            }
                        }, 16);
                    });
                }
            });
        }, { threshold: 0.5 });

        statsObserver.observe(statsSection);
    }

    // =========================================================
    // 5. SMOOTH SCROLL FOR MOUSE INDICATOR
    // =========================================================
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            window.scrollTo({
                top: window.innerHeight,
                behavior: 'smooth'
            });
        });
    }

});
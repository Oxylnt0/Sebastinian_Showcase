// ============================================================
// index.js — Elite Edition
// Author: Sebastinian Showcase
// ============================================================

document.addEventListener("DOMContentLoaded", () => {

    /* ============================================================
       1. HERO SECTION — Smooth Scroll CTA
    ============================================================ */
    const heroCTA = document.querySelector(".hero-cta");
    if (heroCTA) {
        heroCTA.addEventListener("click", (e) => {
            e.preventDefault();
            const target = document.querySelector(heroCTA.getAttribute("href"));
            if (target) {
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    }

    /* ============================================================
       2. PROJECT CARD & FEATURED CARD ANIMATIONS
          Fade-in, Slide-up, and Hover Effects
    ============================================================ */
    const allCards = document.querySelectorAll(".project-card, .featured-card");

    const cardObserver = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("fade-slide-in");
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1 }
    );

    allCards.forEach(card => {
        card.classList.add("pre-fade-slide"); // initial hidden state
        cardObserver.observe(card);
    });

    /* ============================================================
       3. FEATURED PROJECT — Dynamic Highlight (Most Liked)
    ============================================================ */
    const featuredCard = document.querySelector(".featured-card");
    if (featuredCard) {
        const img = featuredCard.querySelector("img");
        // Subtle zoom on hover
        img.addEventListener("mouseenter", () => img.style.transform = "scale(1.05)");
        img.addEventListener("mouseleave", () => img.style.transform = "scale(1)");
    }

    /* ============================================================
       4. PROJECT GRID — Hover Zoom & Overlay Effects
    ============================================================ */
    const projectImages = document.querySelectorAll(".project-media img");
    projectImages.forEach(img => {
        img.addEventListener("mouseenter", () => {
            img.style.transform = "scale(1.08)";
            img.style.transition = "transform 0.4s ease";
        });
        img.addEventListener("mouseleave", () => {
            img.style.transform = "scale(1)";
        });
    });

    /* ============================================================
       5. EXPAND/COLLAPSE LONG PROJECT EXCERPTS
    ============================================================ */
    const projectExcerpts = document.querySelectorAll(".project-excerpt, .featured-summary");
    projectExcerpts.forEach(excerpt => {
        const originalText = excerpt.textContent;
        if (originalText.length > 160) {
            const shortText = originalText.slice(0, 160) + "...";
            excerpt.textContent = shortText;

            const readMore = document.createElement("button");
            readMore.className = "read-more-btn";
            readMore.textContent = "Read More";
            excerpt.after(readMore);

            readMore.addEventListener("click", () => {
                if (excerpt.textContent.length > 160) {
                    excerpt.textContent = shortText;
                    readMore.textContent = "Read More";
                } else {
                    excerpt.textContent = originalText;
                    readMore.textContent = "Show Less";
                }
            });
        }
    });

    /* ============================================================
       6. LAZY LOAD IMAGES — Performance Optimization
    ============================================================ */
    const lazyImages = document.querySelectorAll("img[loading='lazy']");
    if ("IntersectionObserver" in window) {
        const lazyObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.classList.add("visible");
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: "100px 0px" });
        lazyImages.forEach(img => lazyObserver.observe(img));
    }

    /* ============================================================
       7. SDG FILTER INTERACTIVITY (OPTIONAL)
          Sort projects by SDG or author dynamically
    ============================================================ */
    const sdgFilters = document.querySelectorAll(".sdg-filter");
    const projectGrid = document.querySelector(".gallery-grid");
    if (sdgFilters && projectGrid) {
        sdgFilters.forEach(filter => {
            filter.addEventListener("click", () => {
                const filterValue = filter.dataset.sdg; // e.g., "SDG 4"
                const projects = projectGrid.querySelectorAll(".project-card");
                projects.forEach(p => {
                    if (!filterValue || p.dataset.sdg === filterValue) {
                        p.style.display = "block";
                        p.classList.add("fade-slide-in");
                    } else {
                        p.style.display = "none";
                    }
                });
            });
        });
    }

    /* ============================================================
       8. SCROLL-TRIGGERED HEADER SHADOW
    ============================================================ */
    const header = document.querySelector("header");
    window.addEventListener("scroll", () => {
        if (window.scrollY > 20) {
            header.classList.add("scroll-shadow");
        } else {
            header.classList.remove("scroll-shadow");
        }
    });

    /* ============================================================
       9. BACK TO TOP BUTTON — Elite Smooth Scroll
    ============================================================ */
    const backToTop = document.createElement("button");
    backToTop.id = "back-to-top";
    backToTop.textContent = "↑";
    document.body.appendChild(backToTop);

    backToTop.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    window.addEventListener("scroll", () => {
        if (window.scrollY > 500) {
            backToTop.classList.add("visible");
        } else {
            backToTop.classList.remove("visible");
        }
    });

});

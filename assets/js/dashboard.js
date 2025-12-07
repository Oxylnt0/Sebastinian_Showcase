document.addEventListener("DOMContentLoaded", () => {
    // =========================
    // Count-Up Animation for Stats Cards
    // =========================
    const animateCount = (element, endValue, duration = 1200) => {
        let start = 0;
        const increment = endValue / (duration / 16); // approx 60fps
        const counter = () => {
            start += increment;
            if (start < endValue) {
                element.textContent = Math.floor(start);
                requestAnimationFrame(counter);
            } else {
                element.textContent = endValue;
            }
        };
        requestAnimationFrame(counter);
    };

    const cardValues = document.querySelectorAll(".card-value");
    cardValues.forEach(card => {
        const value = parseInt(card.textContent);
        card.textContent = "0"; // start from zero
        animateCount(card, value);
    });

    // =========================
    // Project Card Hover Animation
    // =========================
    const projectCards = document.querySelectorAll(".project-card");
    projectCards.forEach(card => {
        card.addEventListener("mouseenter", () => {
            card.style.transform = "translateY(-5px) scale(1.02)";
            card.style.boxShadow = "0 18px 30px rgba(0,0,0,0.25)";
        });
        card.addEventListener("mouseleave", () => {
            card.style.transform = "translateY(0) scale(1)";
            card.style.boxShadow = "0 8px 18px rgba(0,0,0,0.12)";
        });
    });

    // =========================
    // Status Filter for Recent Projects
    // =========================
    const statusFilterContainer = document.createElement("div");
    statusFilterContainer.classList.add("status-filter-container");
    statusFilterContainer.style.marginBottom = "1.5rem";
    statusFilterContainer.style.textAlign = "center";

    const statuses = ["all", "approved", "pending", "rejected"];
    statuses.forEach(status => {
        const btn = document.createElement("button");
        btn.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        btn.dataset.status = status;
        btn.className = "filter-btn";
        btn.style.margin = "0 0.5rem";
        btn.style.padding = "0.4rem 0.9rem";
        btn.style.border = "none";
        btn.style.borderRadius = "6px";
        btn.style.fontWeight = "600";
        btn.style.cursor = "pointer";
        btn.style.transition = "all 0.3s ease";

        if (status === "all") {
            btn.style.backgroundColor = "#B22222";
            btn.style.color = "#FFD700";
        } else {
            btn.style.backgroundColor = "#eee";
            btn.style.color = "#333";
        }

        btn.addEventListener("mouseenter", () => {
            btn.style.backgroundColor = "#B22222";
            btn.style.color = "#FFD700";
        });

        btn.addEventListener("mouseleave", () => {
            if (btn.dataset.active !== "true") {
                btn.style.backgroundColor = status === "all" ? "#B22222" : "#eee";
                btn.style.color = status === "all" ? "#FFD700" : "#333";
            }
        });

        btn.addEventListener("click", () => {
            document.querySelectorAll(".filter-btn").forEach(b => b.dataset.active = "false");
            btn.dataset.active = "true";
            statuses.forEach(b => {
                if (b !== status) {
                    const otherBtn = document.querySelector(`.filter-btn[data-status='${b}']`);
                    if (otherBtn) {
                        otherBtn.style.backgroundColor = b === "all" ? "#B22222" : "#eee";
                        otherBtn.style.color = b === "all" ? "#FFD700" : "#333";
                    }
                }
            });
            filterProjects(status);
        });

        statusFilterContainer.appendChild(btn);
    });

    const recentProjectsSection = document.querySelector(".recent-projects");
    recentProjectsSection.insertBefore(statusFilterContainer, recentProjectsSection.querySelector(".project-list"));

    const filterProjects = (status) => {
        projectCards.forEach(card => {
            const cardStatus = card.querySelector(".status").textContent.toLowerCase();
            if (status === "all" || cardStatus === status) {
                card.style.display = "flex";
                card.style.opacity = "1";
                card.style.transition = "opacity 0.3s ease";
            } else {
                card.style.opacity = "0";
                setTimeout(() => card.style.display = "none", 300);
            }
        });
    };

    // =========================
    // Button Animations (view-btn & admin-btn)
    // =========================
    const buttons = document.querySelectorAll(".view-btn, .gold-btn");
    buttons.forEach(btn => {
        btn.addEventListener("mouseenter", () => {
            btn.style.transform = "translateY(-3px) scale(1.05)";
            btn.style.boxShadow = "0 6px 15px rgba(0,0,0,0.2)";
        });
        btn.addEventListener("mouseleave", () => {
            btn.style.transform = "translateY(0) scale(1)";
            btn.style.boxShadow = "none";
        });
    });

    // =========================
    // Optional: Smooth Scroll to Admin Section
    // =========================
    const adminBtn = document.querySelector(".admin-link .gold-btn");
    if (adminBtn) {
        adminBtn.addEventListener("click", (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => window.location.href = adminBtn.href, 300);
        });
    }

    // =========================
    // Elite Finish: Subtle Fade-In for Section
    // =========================
    const sections = document.querySelectorAll("main > section");
    sections.forEach((sec, i) => {
        sec.style.opacity = "0";
        sec.style.transform = "translateY(20px)";
        setTimeout(() => {
            sec.style.transition = "all 0.6s ease";
            sec.style.opacity = "1";
            sec.style.transform = "translateY(0)";
        }, i * 150);
    });
});

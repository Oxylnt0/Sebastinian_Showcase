// my_projects.js

document.addEventListener("DOMContentLoaded", () => {
    const projectCards = document.querySelectorAll(".project-card");

    // Hover effect: subtle shadow and scale (for extra polish)
    projectCards.forEach(card => {
        card.addEventListener("mouseenter", () => {
            card.style.transform = "translateY(-5px) scale(1.02)";
            card.style.boxShadow = "0 15px 30px rgba(0,0,0,0.15)";
        });
        card.addEventListener("mouseleave", () => {
            card.style.transform = "translateY(0) scale(1)";
            card.style.boxShadow = "0 6px 18px rgba(0,0,0,0.1)";
        });
    });

    // Optional: Confirm before downloading files
    const downloadLinks = document.querySelectorAll(".project-actions a.gold-btn[href$='.pdf'], .project-actions a.gold-btn[href$='.docx'], .project-actions a.gold-btn[href$='.pptx'], .project-actions a.gold-btn[href$='.txt'], .project-actions a.gold-btn[href$='.zip']");
    downloadLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            const confirmDownload = confirm("Do you want to download this project file?");
            if (!confirmDownload) {
                e.preventDefault();
            }
        });
    });

    // Optional: Filter by SDG (if added in future)
    const filterSelect = document.querySelector("#sdgFilter");
    if (filterSelect) {
        filterSelect.addEventListener("change", () => {
            const selectedSDG = filterSelect.value;
            projectCards.forEach(card => {
                const sdgTag = card.querySelector(".sdg-tag");
                if (sdgTag) {
                    if (selectedSDG === "all" || sdgTag.textContent === selectedSDG) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                }
            });
        });
    }
});

// footer.js

document.addEventListener("DOMContentLoaded", () => {
    const footer = document.querySelector(".site-footer");
    const socialIcons = document.querySelectorAll(".social-icon");

    // Fade-in footer when page loads
    footer.style.opacity = 0;
    footer.style.transform = "translateY(30px)";
    setTimeout(() => {
        footer.style.transition = "all 1s ease-out";
        footer.style.opacity = 1;
        footer.style.transform = "translateY(0)";
    }, 100);

    // Social Icons hover effect (optional extra interactivity)
    socialIcons.forEach(icon => {
        icon.addEventListener("mouseenter", () => {
            icon.style.transform = "translateY(-5px)";
            icon.style.boxShadow = "0 8px 20px rgba(0,0,0,0.25)";
        });
        icon.addEventListener("mouseleave", () => {
            icon.style.transform = "translateY(0)";
            icon.style.boxShadow = "none";
        });
    });
});

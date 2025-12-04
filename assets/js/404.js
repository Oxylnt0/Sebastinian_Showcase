// 404.js

document.addEventListener("DOMContentLoaded", () => {
    const errorNumber = document.querySelector(".error-page h1");
    const errorContainer = document.querySelector(".error-page .container");
    const btnHome = document.querySelector(".btn-home");

    // Fade-in main container
    errorContainer.style.opacity = 0;
    errorContainer.style.transform = "translateY(30px)";
    setTimeout(() => {
        errorContainer.style.transition = "all 1s ease-out";
        errorContainer.style.opacity = 1;
        errorContainer.style.transform = "translateY(0)";
    }, 100);

    // Animate 404 number with slight bounce
    errorNumber.style.opacity = 0;
    errorNumber.style.transform = "scale(0.5)";
    setTimeout(() => {
        errorNumber.style.transition = "all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)";
        errorNumber.style.opacity = 1;
        errorNumber.style.transform = "scale(1)";
    }, 500);

    // Button hover effects (optional extra interactivity)
    btnHome.addEventListener("mouseenter", () => {
        btnHome.style.transform = "translateY(-4px)";
        btnHome.style.boxShadow = "0 10px 25px rgba(0,0,0,0.3)";
    });

    btnHome.addEventListener("mouseleave", () => {
        btnHome.style.transform = "translateY(0)";
        btnHome.style.boxShadow = "0 6px 15px rgba(0,0,0,0.2)";
    });
});

// ==============================
// INDEX.JS — Sebastinian Showcase
// Elite Magazine-style University Portal
// ==============================

document.addEventListener("DOMContentLoaded", () => {

// ===== HERO ANIMATIONS =====
const heroTitle = document.querySelector(".hero h1");
const heroSubtitle = document.querySelector(".hero p");

if(heroTitle && heroSubtitle){
heroTitle.style.opacity = 0;
heroTitle.style.transform = "translateY(-20px)";
heroSubtitle.style.opacity = 0;
heroSubtitle.style.transform = "translateY(20px)";

setTimeout(() => {
  heroTitle.style.transition = "all 1s ease-out";
  heroTitle.style.opacity = 1;
  heroTitle.style.transform = "translateY(0)";
}, 200);

setTimeout(() => {
  heroSubtitle.style.transition = "all 1s ease-out";
  heroSubtitle.style.opacity = 1;
  heroSubtitle.style.transform = "translateY(0)";
}, 600);


}

// ===== PROJECT CARDS FADE-IN =====
const cards = document.querySelectorAll(".project-card");
const observerOptions = { threshold: 0.1 };

const cardObserver = new IntersectionObserver((entries, observer) => {
entries.forEach(entry => {
if(entry.isIntersecting){
entry.target.classList.add("fade-in");
observer.unobserve(entry.target);
}
});
}, observerOptions);

cards.forEach(card => cardObserver.observe(card));

// ===== HERO PARALLAX EFFECT =====
const hero = document.querySelector(".hero");
if(hero){
window.addEventListener("scroll", () => {
const scrollY = window.scrollY;
hero.style.backgroundPosition = `center ${scrollY * 0.3}px`;
});
}

// ===== CARD HOVER EFFECTS =====
cards.forEach(card => {
card.addEventListener("mousemove", (e) => {
const rect = card.getBoundingClientRect();
const x = e.clientX - rect.left;
const y = e.clientY - rect.top;

  const rotateX = ((y / rect.height) - 0.5) * 8; // max 8 deg
  const rotateY = ((x / rect.width) - 0.5) * 8;  // max 8 deg

  card.style.transform = `translateY(-10px) rotateX(${-rotateX}deg) rotateY(${rotateY}deg)`;
  card.style.transition = "transform 0.1s ease-out";
});

card.addEventListener("mouseleave", () => {
  card.style.transform = "translateY(-10px) rotateX(0deg) rotateY(0deg)";
  card.style.transition = "transform 0.35s ease-out";
});


});

// ===== SMOOTH SCROLL FOR LINKS =====
const links = document.querySelectorAll('a[href^="#"]');
links.forEach(link => {
link.addEventListener("click", e => {
e.preventDefault();
const target = document.querySelector(link.getAttribute("href"));
if(target){
target.scrollIntoView({ behavior: "smooth" });
}
});
});

});
/* =========================================== */
/* Premium Admin Register JS – Sebastinian Showcase */
/* Fully interactive, UX-focused, modern       */
/* =========================================== */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("adminRegisterForm");
    const feedback = document.getElementById("feedback");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");
    const togglePasswords = document.querySelectorAll(".toggle-password");
    const passwordStrengthBar = document.getElementById("passwordStrength");

    // -----------------------------
    // Premium Show/Hide Password Toggle (SVG Icons)
    // -----------------------------
    togglePasswords.forEach(toggle => {
        toggle.addEventListener("click", () => {
            const input = document.querySelector(toggle.dataset.target);
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";

            // SVG icons for professional look
            toggle.innerHTML = isPassword
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#B22222" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#B22222" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8 1.73-3.48 4.72-6.2 8.27-7.32M1 1l22 22"/></svg>`;
        });
    });

    // -----------------------------
    // Floating Label UX Effect
    // -----------------------------
    form.querySelectorAll("input").forEach(input => {
        input.addEventListener("focus", () => input.classList.add("focused"));
        input.addEventListener("blur", () => {
            if (!input.value) input.classList.remove("focused");
        });
    });

    // -----------------------------
    // Password Strength Checker
    // -----------------------------
    passwordInput.addEventListener("input", () => {
        const val = passwordInput.value;
        let score = 0;

        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ["#B22222", "#FFA500", "#FFD700", "#228B22"];
        passwordStrengthBar.style.width = `${(score * 25)}%`;
        passwordStrengthBar.style.backgroundColor = colors[score];
    });

    // -----------------------------
    // Confirm Password Validation
    // -----------------------------
    const validatePasswords = () => {
        if (confirmPasswordInput.value && confirmPasswordInput.value !== passwordInput.value) {
            confirmPasswordInput.setCustomValidity("Passwords do not match");
        } else {
            confirmPasswordInput.setCustomValidity("");
        }
    };
    passwordInput.addEventListener("input", validatePasswords);
    confirmPasswordInput.addEventListener("input", validatePasswords);

    // -----------------------------
    // Feedback Animation Function
    // -----------------------------
    const showFeedback = (message, type = "error") => {
        feedback.textContent = message;
        feedback.className = `feedback ${type}`;
        feedback.style.opacity = 1;
        feedback.style.transform = "translateY(0)";
        setTimeout(() => {
            feedback.style.opacity = 0;
            feedback.style.transform = "translateY(-10px)";
        }, 5000);
    };

    // -----------------------------
    // AJAX Form Submission
    // -----------------------------
    form.addEventListener("submit", async e => {
        e.preventDefault();

        const submitBtn = document.getElementById("submitBtn");
        submitBtn.disabled = true;
        submitBtn.textContent = "Registering...";

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, { method: "POST", body: formData });
            const result = await response.json();

            if (result.status === "success") {
                showFeedback(result.message || "Admin registered successfully!", "success");
                form.reset();
                passwordStrengthBar.style.width = "0";
                form.querySelectorAll("input").forEach(i => i.classList.remove("focused"));
            } else {
                form.classList.add("shake");
                setTimeout(() => form.classList.remove("shake"), 500);
                showFeedback(result.message || "Something went wrong", "error");
            }

        } catch (error) {
            showFeedback("Server error: " + error.message, "error");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = "Register Admin";
        }
    });

});

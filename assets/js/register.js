/**
 * assets/js/register.js
 * The Ultimate Registration Logic
 * * Features:
 * - Robust Password Visibility Toggling (Works with SVG or I tags)
 * - Real-time Password Strength Analysis
 * - Secure AJAX Form Submission
 * - Interactive UI Feedback (Loading states, Shaking on error)
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 1. GLOBAL ELEMENTS
    // =========================================================
    const form = document.getElementById("registerForm");
    const submitBtn = document.getElementById("submitBtn");
    const feedback = document.getElementById("formFeedback");
    
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirm_password");
    
    const strengthBar = document.getElementById("strengthBar");
    const strengthText = document.getElementById("strengthText");
    
    // Select all toggle buttons
    const toggleBtns = document.querySelectorAll(".toggle-password");


    // =========================================================
    // 2. ROBUST PASSWORD TOGGLE LOGIC
    // =========================================================
    toggleBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            // Prevent default behavior (submission or focus jump)
            e.preventDefault();
            e.stopPropagation();

            // 1. Find the parent wrapper relative to this button
            const wrapper = btn.closest('.input-wrapper');
            
            // 2. Find the input and icon within this specific wrapper
            const input = wrapper.querySelector('input');
            const icon = btn.querySelector('i, svg'); // Works for both FontAwesome <i> and <svg>

            if (!input || !icon) return; // Safety check

            // 3. Toggle Logic
            if (input.type === "password") {
                // Show Password
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                // Hide Password
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    });


    // =========================================================
    // 3. PASSWORD STRENGTH METER
    // =========================================================
    if (passwordInput) {
        passwordInput.addEventListener("input", () => {
            const val = passwordInput.value;
            const result = calculateStrength(val);
            
            // Update the Bar
            strengthBar.style.width = result.width;
            strengthBar.style.backgroundColor = result.color;
            
            // Update the Text
            strengthText.textContent = result.text;
            strengthText.style.color = result.color;
        });
    }

    /**
     * Helper to calculate strength score (0-4)
     */
    function calculateStrength(val) {
        let score = 0;
        if (val.length > 5) score++;           // Length > 5
        if (/[A-Z]/.test(val)) score++;        // Has Uppercase
        if (/[0-9]/.test(val)) score++;        // Has Number
        if (/[^A-Za-z0-9]/.test(val)) score++; // Has Special Char

        // Logic Mapping
        if (val.length > 0 && val.length < 6) {
            return { width: "20%", color: "#E74C3C", text: "Too Short" }; 
        } else if (score === 1) {
            return { width: "40%", color: "#E67E22", text: "Weak" };      
        } else if (score === 2) {
            return { width: "60%", color: "#F1C40F", text: "Fair" };      
        } else if (score === 3) {
            return { width: "80%", color: "#27AE60", text: "Good" };      
        } else if (score >= 4) {
            return { width: "100%", color: "#2ECC71", text: "Strong" };   
        } else {
            return { width: "0%", color: "#E0E0E0", text: "Password Strength" }; // Default
        }
    }


    // =========================================================
    // 4. AJAX FORM SUBMISSION
    // =========================================================
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            // --- A. Reset UI ---
            hideFeedback();
            
            // --- B. Client Validation ---
            // 1. Check Matching Passwords
            if (passwordInput.value !== confirmInput.value) {
                showFeedback("Passwords do not match.", "error");
                shakeElement(confirmInput.closest('.input-wrapper'));
                return;
            }

            // 2. Check Minimum Length
            if (passwordInput.value.length < 6) {
                showFeedback("Password must be at least 6 characters.", "error");
                shakeElement(passwordInput.closest('.input-wrapper'));
                return;
            }

            // --- C. Set Loading State ---
            const originalBtnHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Creating Account...';

            // --- D. Send Data ---
            const formData = new FormData(form);

            try {
                const response = await fetch("../api/auth/register.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.status === "success") {
                    // SUCCESS
                    showFeedback("Account created! Redirecting to login...", "success");
                    form.reset();
                    strengthBar.style.width = "0%";
                    
                    // Redirect after 1.5 seconds
                    setTimeout(() => {
                        window.location.href = "login.php";
                    }, 1500);

                } else {
                    // API ERROR (e.g., Username taken)
                    showFeedback(data.message || "Registration failed.", "error");
                    resetButton(originalBtnHTML);
                    
                    // Shake specific fields if mentioned in error
                    if(data.message.toLowerCase().includes("username")) {
                        shakeElement(document.getElementById("username").closest('.input-wrapper'));
                    }
                }

            } catch (error) {
                // NETWORK ERROR
                console.error("Error:", error);
                showFeedback("Network error. Please try again later.", "error");
                resetButton(originalBtnHTML);
            }
        });
    }


    // =========================================================
    // 5. UTILITY FUNCTIONS
    // =========================================================
    
    function showFeedback(message, type) {
        if (!feedback) return;
        feedback.textContent = message;
        // Reset classes then add the specific type
        feedback.className = "form-feedback"; 
        feedback.classList.add(type); // .error or .success
        feedback.style.display = "block";
    }

    function hideFeedback() {
        if (!feedback) return;
        feedback.style.display = "none";
        feedback.className = "form-feedback";
    }

    function resetButton(originalHTML) {
        if (!submitBtn) return;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }

    // Adds a temporary shake animation class
    function shakeElement(element) {
        if (!element) return;
        // Remove animation if present to allow re-triggering
        element.style.animation = 'none';
        element.offsetHeight; // Trigger reflow
        
        element.style.animation = "shake 0.4s ease-in-out";
        
        // Remove after animation completes
        setTimeout(() => {
            element.style.animation = "";
        }, 400);
    }
    
    // Inject Shake Keyframes dynamically if not in CSS
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(styleSheet);
});
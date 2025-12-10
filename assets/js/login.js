/**
 * assets/js/login.js
 * The Ultimate Login Logic
 * * Features:
 * - Robust Password Visibility Toggling
 * - Secure AJAX Form Submission
 * - Intelligent Role-Based Redirect
 * - Interactive UI Feedback (Loading states, Shaking on error)
 */

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================================
    // 1. GLOBAL ELEMENTS
    // =========================================================
    const form = document.getElementById("loginForm");
    const submitBtn = document.getElementById("submitBtn");
    const feedback = document.getElementById("formFeedback");
    
    // Select all toggle buttons (class="toggle-password")
    const toggleBtns = document.querySelectorAll(".toggle-password");


    // =========================================================
    // 2. ROBUST PASSWORD TOGGLE LOGIC
    // =========================================================
    toggleBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            // Stop the click from submitting the form or bubbling up
            e.preventDefault();
            e.stopPropagation();

            // 1. Find the parent wrapper relative to this button
            const wrapper = btn.closest('.input-wrapper');
            
            // 2. Find the input and icon within this specific wrapper
            const input = wrapper.querySelector('input');
            const icon = btn.querySelector('i, svg'); // Supports both <i> and <svg> tags

            // Safety check: ensure elements exist
            if (!input || !icon) {
                console.warn("Toggle Error: Input or Icon not found.");
                return; 
            }

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
    // 3. AJAX FORM SUBMISSION
    // =========================================================
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            // --- A. Reset UI ---
            hideFeedback();
            
            // --- B. Set Loading State ---
            const originalBtnHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Logging in...';

            // --- C. Send Data ---
            const formData = new FormData(form);

            try {
                const response = await fetch("../api/auth/login.php", {
                    method: "POST",
                    body: formData
                });

                // Parse the JSON response
                const data = await response.json();

                if (data.status === "success") {
                    // SUCCESS
                    showFeedback("Login successful! Redirecting...", "success");
                    
                    // Intelligent Redirect based on Role
                    // data.data should contain { role: 'admin' } or { role: 'student' }
                    const role = (data.data && data.data.role) ? data.data.role : 'student';
                    
                    setTimeout(() => {
                        if (role === 'admin') {
                            window.location.href = "admin/admin_dashboard.php"; 
                            // Note: Adjust path if your login.php is already inside /pages/
                            // If login.php is in /pages/, use: "admin/admin_dashboard.php"
                        } else {
                            window.location.href = "dashboard.php";
                        }
                    }, 1000);

                } else {
                    // API ERROR (Wrong password/username)
                    showFeedback(data.message || "Invalid credentials.", "error");
                    shakeElement(form); // Shake the whole form
                    resetButton(originalBtnHTML);
                }

            } catch (error) {
                // NETWORK ERROR
                console.error("Login Error:", error);
                showFeedback("Server connection error. Please try again.", "error");
                shakeElement(form);
                resetButton(originalBtnHTML);
            }
        });
    }


    // =========================================================
    // 4. UTILITY FUNCTIONS
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
        
        // Add css keyframe animation
        element.style.animation = "shake 0.4s ease-in-out";
        
        // Remove after animation completes
        setTimeout(() => {
            element.style.animation = "";
        }, 400);
    }
    
    // Inject Shake Keyframes dynamically (ensures animation works without adding to CSS file)
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
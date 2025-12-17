document.addEventListener("DOMContentLoaded", () => {
    
    // Elements
    const form = document.getElementById("registerForm");
    const submitBtn = document.getElementById("submitBtn");
    const feedback = document.getElementById("formFeedback");
    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirm_password");
    const emailInput = document.getElementById("email");
    const toggleBtns = document.querySelectorAll(".toggle-password");
    
    // Password Checklist Elements
    const checklistBox = document.getElementById("passwordChecklist");
    const ruleLength = document.getElementById("rule-length");
    const ruleUpper = document.getElementById("rule-upper");
    const ruleSpecial = document.getElementById("rule-special");

    let isSubmitting = false; 

    // 1. Password Toggle
    toggleBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault(); 
            const input = btn.closest('.input-wrapper').querySelector('input');
            const icon = btn.querySelector('i, svg');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        });
    });

    // 2. Real-Time Password Validation Logic
    if (passwordInput && checklistBox) {
        
        // Show requirements on focus
        passwordInput.addEventListener("focus", () => {
            checklistBox.style.display = "block";
        });

        passwordInput.addEventListener("blur", () => {
            checklistBox.style.display = "none";
        });

        // Check rules while typing
        passwordInput.addEventListener("input", () => {
            checklistBox.style.display = "block"; 
            const val = passwordInput.value;

            // UPDATED RULE: Length 12+
            if (val.length >= 12) {
                setValid(ruleLength, true);
            } else {
                setValid(ruleLength, false);
            }

            // Rule 2: One Uppercase
            if (/[A-Z]/.test(val)) {
                setValid(ruleUpper, true);
            } else {
                setValid(ruleUpper, false);
            }

            // Rule 3: One Special Character
            if (/[\W_]/.test(val)) {
                setValid(ruleSpecial, true);
            } else {
                setValid(ruleSpecial, false);
            }
        });
    }

    function setValid(element, isValid) {
        const icon = element.querySelector("i");
        if (isValid) {
            element.classList.remove("invalid");
            element.classList.add("valid");
            icon.className = "fa-solid fa-check";
        } else {
            element.classList.remove("valid");
            element.classList.add("invalid");
            icon.className = "fa-solid fa-circle"; 
        }
    }

    // 3. FORM SUBMISSION
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (isSubmitting) return; 
            hideFeedback();

            // --- A. Email Validation (@sscr.edu) ---
            const emailVal = emailInput.value.trim();
            if (!emailVal.endsWith("@sscr.edu")) {
                showFeedback("Email must end with @sscr.edu", "error");
                shakeElement(emailInput.closest('.input-wrapper'));
                return;
            }

            // --- B. Password Validation ---
            const passVal = passwordInput.value;
            // UPDATED REGEX: 12 chars minimum
            const passRegex = /^(?=.*[A-Z])(?=.*[\W_]).{12,}$/;
            
            if (!passRegex.test(passVal)) {
                showFeedback("Password does not meet requirements (Min 12 chars).", "error");
                checklistBox.style.display = "block"; 
                shakeElement(passwordInput.closest('.input-wrapper'));
                return;
            }

            if (passVal !== confirmInput.value) {
                showFeedback("Passwords do not match.", "error");
                shakeElement(confirmInput.closest('.input-wrapper'));
                return;
            }

            // --- C. Submit ---
            isSubmitting = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Processing...';

            const formData = new FormData(form);

            try {
                const response = await fetch("../api/auth/register.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.status === "success") {
                    if (data.action === "verify_otp") {
                        showOtpInterface(data.email);
                    } else {
                        showFeedback("Account created!", "success");
                        setTimeout(() => window.location.href = "login.php", 1500);
                    }
                } else {
                    showFeedback(data.message, "error");
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    isSubmitting = false; 
                }

            } catch (error) {
                console.error(error);
                showFeedback("Network error.", "error");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                isSubmitting = false;
            }
        });
    }

    // --- Helpers ---
    function showFeedback(msg, type) {
        if (!feedback) return;
        feedback.textContent = msg;
        feedback.className = `form-feedback ${type}`;
        feedback.style.display = "block";
    }

    function hideFeedback() {
        if (feedback) feedback.style.display = "none";
    }

    function shakeElement(element) {
        if (!element) return;
        element.style.animation = 'none';
        element.offsetHeight; 
        element.style.animation = "shake 0.4s ease-in-out";
    }

    function showOtpInterface(email) {
        const container = document.querySelector('.register-wrapper');
        container.innerHTML = `
            <section class="register-form-box" style="text-align: center;">
                <div class="form-header">
                    <i class="fa-solid fa-envelope-circle-check" style="font-size: 3rem; color: #D4AF37;"></i>
                    <h2>Verify Email</h2>
                    <p>Code sent to <strong>${email}</strong></p>
                </div>
                <div class="input-wrapper" style="margin: 20px 0;">
                    <input type="text" id="otpCode" maxlength="6" placeholder="000000" 
                        style="width: 100%; padding: 15px; font-size: 1.5rem; text-align: center; letter-spacing: 5px; font-weight: bold; border: 2px solid #eee; border-radius: 8px;">
                </div>
                <button id="btnVerify" class="btn-register"><span class="btn-text">Verify</span></button>
                <div id="otpMessage" style="margin-top: 15px;"></div>
            </section>
        `;

        const btnVerify = document.getElementById('btnVerify');
        const otpInput = document.getElementById('otpCode');
        const msgBox = document.getElementById('otpMessage');

        btnVerify.addEventListener('click', async () => {
            const code = otpInput.value.trim();
            if(code.length !== 6) {
                msgBox.textContent = "Enter 6-digit code";
                msgBox.style.color = "red";
                return;
            }
            btnVerify.disabled = true;
            btnVerify.innerText = "Verifying...";

            try {
                const fd = new FormData();
                fd.append('email', email);
                fd.append('otp', code);
                const res = await fetch('../api/auth/verify_otp.php', { method: 'POST', body: fd });
                const d = await res.json();

                if (d.status === 'success') {
                    msgBox.textContent = "Verified! Redirecting...";
                    msgBox.style.color = "green";
                    setTimeout(() => window.location.href = 'login.php', 1500);
                } else {
                    msgBox.textContent = d.message;
                    msgBox.style.color = "red";
                    btnVerify.disabled = false;
                    btnVerify.innerText = "Verify";
                }
            } catch (err) {
                msgBox.textContent = "Network Error";
                btnVerify.disabled = false;
            }
        });
    }
});
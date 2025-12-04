document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.getElementById("register-form");
    const messageDiv = document.getElementById("register-message");

    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault(); // prevent page reload

        // Clear previous message
        messageDiv.textContent = "";
        messageDiv.style.color = "#B22222"; // default red for errors

        const formData = new FormData(registerForm);

        // Basic client-side validation
        const password = formData.get("password");
        if (password.length < 6) {
            messageDiv.textContent = "Password must be at least 6 characters.";
            return;
        }

        try {
            const response = await fetch(registerForm.action, {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.status === "success") {
                messageDiv.style.color = "#FFD700"; // gold for success
                messageDiv.textContent = result.message;

                // Redirect to login after short delay
                setTimeout(() => {
                    window.location.href = "login.php";
                }, 1200);
            } else {
                messageDiv.textContent = result.message;
            }
        } catch (error) {
            console.error("Registration Error:", error);
            messageDiv.textContent = "An unexpected error occurred. Please try again.";
        }
    });
});

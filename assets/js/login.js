document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const messageDiv = document.getElementById("login-message");

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault(); // prevent default form submission

        // Clear previous messages
        messageDiv.textContent = "";
        messageDiv.style.color = "#B22222"; // red for errors by default

        const formData = new FormData(loginForm);

        try {
            const response = await fetch(loginForm.action, {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.status === "success") {
                // Show success message
                messageDiv.style.color = "#FFD700"; // gold for success
                messageDiv.textContent = result.message;

                // Redirect to dashboard after short delay
                setTimeout(() => {
                    window.location.href = "dashboard.php";
                }, 1000);
            } else {
                // Show error message
                messageDiv.textContent = result.message;
            }
        } catch (error) {
            console.error("Login Error:", error);
            messageDiv.textContent = "An unexpected error occurred. Please try again.";
        }
    });
});

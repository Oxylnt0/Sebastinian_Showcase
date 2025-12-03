// register.js

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("register-form");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const btn = document.getElementById("btn-register");
        btn.disabled = true;
        btn.textContent = "Registering...";

        try {
            const res = await fetch("../api/auth/register.php", {
                method: "POST",
                body: formData
            });

            const data = await res.json();

            if (data.status === "success") {
                alert("Registration successful! Redirecting to dashboard...");
                window.location.href = "dashboard.php";
            } else {
                alert(data.message || "Registration failed.");
            }

        } catch (err) {
            console.error(err);
            alert("An error occurred. Please try again.");
        } finally {
            btn.disabled = false;
            btn.textContent = "Register";
        }
    });
});

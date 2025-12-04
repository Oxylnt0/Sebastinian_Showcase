// profile.js

document.addEventListener("DOMContentLoaded", () => {
    const profileForm = document.getElementById("profileForm");
    const profileImageInput = document.getElementById("profileImage");
    const profileImagePreview = document.getElementById("profileImagePreview");
    const responseMessage = document.getElementById("responseMessage");

    // Preview selected profile image
    profileImageInput.addEventListener("change", (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                profileImagePreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle profile update via AJAX
    profileForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(profileForm);
        if (profileImageInput.files.length > 0) {
            formData.append("profile_image", profileImageInput.files[0]);
        }

        responseMessage.textContent = "Updating profile...";
        responseMessage.style.color = "#b71c1c";

        try {
            const res = await fetch("../api/utils/update_profile.php", {
                method: "POST",
                body: formData,
            });

            const data = await res.json();

            if (data.status === "success") {
                responseMessage.textContent = data.message;
                responseMessage.style.color = "#ffb300"; // Gold for success
            } else {
                responseMessage.textContent = data.message;
                responseMessage.style.color = "#b71c1c"; // Red for errors
            }
        } catch (error) {
            responseMessage.textContent = "An unexpected error occurred.";
            responseMessage.style.color = "#b71c1c";
            console.error(error);
        }
    });
});

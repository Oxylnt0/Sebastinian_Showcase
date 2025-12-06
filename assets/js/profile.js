// profile.js - Final Version
document.addEventListener("DOMContentLoaded", () => {
    const profileForm = document.getElementById("profileForm");
    const profileImageInput = document.getElementById("profileImage");
    const profileImagePreview = document.getElementById("profileImagePreview");
    const responseMessage = document.getElementById("responseMessage");

    if (!profileForm || !profileImageInput || !profileImagePreview || !responseMessage) return;

    // --- Drag & Drop Preview ---
    const dropzone = profileImageInput.parentElement;
    dropzone.classList.add("dropzone");

    // Drag hover effect
    ["dragover", "dragenter"].forEach(event => {
        dropzone.addEventListener(event, e => {
            e.preventDefault();
            dropzone.classList.add("hover");
        });
    });

    // Remove hover effect
    ["dragleave", "dragend", "drop"].forEach(event => {
        dropzone.addEventListener(event, e => {
            e.preventDefault();
            dropzone.classList.remove("hover");
        });
    });

    // Handle dropped file
    dropzone.addEventListener("drop", e => {
        e.preventDefault();
        if (e.dataTransfer.files.length > 0) {
            profileImageInput.files = e.dataTransfer.files;
            showImagePreview(e.dataTransfer.files[0]);
        }
    });

    // --- Show Image Preview ---
    function showImagePreview(file) {
        if (!file) {
            profileImagePreview.src = "../uploads/profile_images/default.png";
            return;
        }
        if (!file.type.startsWith("image/")) {
            responseMessage.textContent = "Invalid image file type.";
            responseMessage.style.color = "red";
            return;
        }
        const reader = new FileReader();
        reader.onload = event => {
            profileImagePreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Preview on manual file select
    profileImageInput.addEventListener("change", () => {
        if (profileImageInput.files.length > 0) {
            showImagePreview(profileImageInput.files[0]);
        }
    });

    // --- AJAX Form Submission ---
    profileForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(profileForm);
        if (profileImageInput.files.length > 0) {
            formData.append("profile_image", profileImageInput.files[0]);
        }

        responseMessage.textContent = "Updating profile...";
        responseMessage.style.color = "red";

        try {
            const res = await fetch("../api/utils/update_profile.php", {
                method: "POST",
                body: formData
            });

            let data;
            try {
                data = await res.json();
            } catch (err) {
                const text = await res.text();
                console.error("Invalid JSON response:", text);
                responseMessage.textContent = "Server returned invalid response. Check console.";
                return;
            }

            if (data.status === "success") {
                responseMessage.textContent = data.message;
                responseMessage.style.color = "#ffb300"; // Gold for success
            } else {
                responseMessage.textContent = data.message;
                responseMessage.style.color = "red";
            }
        } catch (err) {
            console.error("Request failed:", err);
            responseMessage.textContent = "An unexpected error occurred.";
            responseMessage.style.color = "red";
        }
    });
});

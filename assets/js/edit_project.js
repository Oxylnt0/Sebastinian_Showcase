// ===============================
// Ultimate Edit Project JS
// ===============================

document.addEventListener("DOMContentLoaded", () => {
    const imageInput = document.getElementById("image");
    const imagePreview = document.getElementById("image-preview");
    const fileInput = document.getElementById("file");
    const editForm = document.getElementById("edit-project-form");
    const errorContainer = document.querySelector(".error-messages");
    
    // Allowed types
    const allowedImageTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    const allowedFileTypes = [
        "application/pdf",
        "application/zip",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
    ];

    const maxImageSize = 2 * 1024 * 1024; // 2MB
    const maxFileSize = 50 * 1024 * 1024; // 50MB

    // ===============================
    // Image Preview
    // ===============================
    imageInput.addEventListener("change", () => {
        const file = imageInput.files[0];
        if (file) {
            if (!allowedImageTypes.includes(file.type)) {
                showError("Invalid image type. Allowed: JPG, PNG, GIF, WEBP.");
                imageInput.value = "";
                return;
            }
            if (file.size > maxImageSize) {
                showError("Image exceeds maximum size of 2MB.");
                imageInput.value = "";
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    });

    // ===============================
    // File Name Display
    // ===============================
    fileInput.addEventListener("change", () => {
        const file = fileInput.files[0];
        if (file) {
            if (!allowedFileTypes.includes(file.type)) {
                showError("Invalid file type. Allowed: PDF, ZIP, DOC, DOCX.");
                fileInput.value = "";
                return;
            }
            if (file.size > maxFileSize) {
                showError("File exceeds maximum size of 50MB.");
                fileInput.value = "";
                return;
            }
            alert(`Selected file: ${file.name}`);
        }
    });

    // ===============================
    // Form Validation Before Submit
    // ===============================
    editForm.addEventListener("submit", (e) => {
        const title = document.getElementById("title").value.trim();
        const description = document.getElementById("description").value.trim();
        let errors = [];

        if (!title) errors.push("Title is required.");
        if (title.length > 150) errors.push("Title cannot exceed 150 characters.");
        if (!description) errors.push("Description is required.");

        if (imageInput.files[0] && imageInput.files[0].size > maxImageSize) {
            errors.push("Selected image exceeds 2MB.");
        }

        if (fileInput.files[0] && fileInput.files[0].size > maxFileSize) {
            errors.push("Selected file exceeds 50MB.");
        }

        if (errors.length > 0) {
            e.preventDefault();
            showError(errors.join(" "));
        }
    });

    // ===============================
    // Show Error Function
    // ===============================
    function showError(message) {
        if (errorContainer) {
            errorContainer.innerHTML = `<p class="error">${message}</p>`;
            errorContainer.scrollIntoView({ behavior: "smooth", block: "center" });
        } else {
            alert(message);
        }
    }
});

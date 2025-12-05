// ================================
// Upload Project JS - Sebastinian Showcase
// Handles AJAX form submission with file uploads
// ================================

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("uploadForm");
    const submitBtn = form.querySelector(".btn-submit");
    const feedback = document.getElementById("feedback");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // Prevent default form submission

        // Clear previous feedback
        feedback.textContent = "";
        feedback.classList.remove("success", "error");

        // Disable button and show uploading text
        submitBtn.disabled = true;
        submitBtn.textContent = "Uploading...";

        // Prepare FormData for AJAX request
        const formData = new FormData(form);

        try {
            const response = await fetch("../projects/upload_project.php", {
                method: "POST",
                body: formData
            });

            const result = await response.json();

            if (result.status === "success") {
                feedback.textContent = result.message || "Project uploaded successfully!";
                feedback.classList.add("success");

                // Reset the form after successful upload
                form.reset();
            } else {
                feedback.textContent = result.message || "An error occurred while uploading.";
                feedback.classList.add("error");
            }
        } catch (error) {
            console.error("Upload error:", error);
            feedback.textContent = "Server error. Please try again later.";
            feedback.classList.add("error");
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = "Upload Project";
        }
    });
});
